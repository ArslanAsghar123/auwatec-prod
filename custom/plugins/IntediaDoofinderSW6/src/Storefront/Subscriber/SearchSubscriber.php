<?php declare(strict_types=1);

namespace Intedia\Doofinder\Storefront\Subscriber;

use Intedia\Doofinder\Core\Content\Settings\Service\BotDetectionHandler;
use Intedia\Doofinder\Core\Content\Settings\Service\SettingsHandler;
use Intedia\Doofinder\Doofinder\Api\Search;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Search\SearchPageLoadedEvent;
use Shopware\Storefront\Page\Suggest\SuggestPageLoadedEvent;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class SearchSubscriber implements EventSubscriberInterface
{
    const IS_DOOFINDER_TERM = 'doofinder-search';

    /** @var SystemConfigService */
    protected $systemConfigService;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Search */
    protected $searchApi;

    /** @var array */
    protected $doofinderIds;

    /** @var integer */
    protected $shopwareLimit;

    /** @var integer */
    protected $shopwareOffset;

    /** @var bool */
    protected $isScoreSorting;

    /** @var bool */
    protected $isSuggestCall = false;

    private EntityRepository $salesChannelDomainRepository;
    private EntityRepository $productRepository;
    private SettingsHandler $settingsHandler;

    /**
     * SearchSubscriber constructor.
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     * @param Search $searchApi
     * @param EntityRepository $salesChannelDomainRepository
     * @param EntityRepository $productRepository
     * @param SettingsHandler $settingsHandler
     */
    public function __construct(
        SystemConfigService $systemConfigService,
        LoggerInterface $logger,
        Search   $searchApi,
        EntityRepository $salesChannelDomainRepository,
        EntityRepository $productRepository,
        SettingsHandler $settingsHandler
    ) {
        $this->systemConfigService          = $systemConfigService;
        $this->logger                       = $logger;
        $this->searchApi                    = $searchApi;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->productRepository            = $productRepository;
        $this->settingsHandler              = $settingsHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductSearchCriteriaEvent::class  => 'onSearchCriteriaEvent',
            SearchPageLoadedEvent::class       => 'onSearchPageLoadedEvent',
            ProductSuggestCriteriaEvent::class => 'onSuggestCriteriaEvent',
            SuggestPageLoadedEvent::class      => 'onSuggestPageLoadedEvent',
            FooterPageletLoadedEvent::class    => 'generateCorrectDooFinderData'
        ];
    }

    public function generateCorrectDooFinderData(FooterPageletLoadedEvent $event)
    {
        $criteria = new Criteria([$event->getSalesChannelContext()->getDomainId()]);
        $criteria->addAssociation('language')
            ->addAssociation('currency')
            ->addAssociation('language.locale')
            ->addAssociation('domains.language.locale');

        $domain = $this->salesChannelDomainRepository->search($criteria, Context::createDefaultContext())->first();

        $doofinderLayer = $this->settingsHandler->getDooFinderLayer($domain);
        $hashId = '';
        $storeId = '';
        if ($doofinderLayer) {
            $hashId = $doofinderLayer->getDooFinderHashId();
            $storeId = $doofinderLayer->getDoofinderStoreId();
        }

        $event->getPagelet()->addExtension('doofinder', new ArrayStruct(['hashId' => $hashId, 'storeId' => $storeId]));
    }

    /**
     * @param ProductSearchCriteriaEvent $event
     */
    public function onSearchCriteriaEvent(ProductSearchCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();
        $request  = $event->getRequest();
        $context  = $event->getSalesChannelContext();

        $this->handleWithDoofinder($context, $request, $criteria);
    }

    /**
     * @param ProductSuggestCriteriaEvent $event
     */
    public function onSuggestCriteriaEvent(ProductSuggestCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();
        $request  = $event->getRequest();
        $context  = $event->getSalesChannelContext();
        $this->isSuggestCall = true;

        $this->handleWithDoofinder($context, $request, $criteria);
    }

    /**
     * @param SalesChannelContext $context
     * @param Request $request
     * @param Criteria $criteria
     */
    protected function handleWithDoofinder(SalesChannelContext $context, Request $request, Criteria $criteria): void
    {
        $searchSubscriberActivationMode = $this->getDoofinderSearchSubscriberActivationMode($context);

        // inactive for bots
        if ($searchSubscriberActivationMode == 2 && BotDetectionHandler::checkIfItsBot($request->headers->get('User-Agent'))) {
            return;
        } elseif ($searchSubscriberActivationMode == 3) { // inactive for all
            return;
        }

        if ($this->systemConfigService->get('IntediaDoofinderSW6.config.doofinderEnabled', $context->getSalesChannel()->getId())) {

            $term = $request->query->get('search');

            if ($term) {
                $this->doofinderIds = $this->searchApi->queryIds($term, $context);

                $this->storeShopwareLimitAndOffset($criteria);

                if (!empty($this->doofinderIds)) {

                    $this->manipulateCriteriaLimitAndOffset($criteria);
                    $this->resetCriteriaFiltersQueriesAndSorting($criteria);
                    $this->addProductNumbersToCriteria($criteria);

                    if ($this->isSuggestCall) {
                        $criteria->setTerm(null);
                    }
                    else {
                        $criteria->setTerm(self::IS_DOOFINDER_TERM);
                    }
                }
            }
        }
    }

    /**
     * @param Criteria $criteria
     */
    protected function resetCriteriaFiltersQueriesAndSorting(Criteria $criteria): void
    {
        $criteria->resetFilters();
        $criteria->resetQueries();

        if ($this->isSuggestCall || $this->checkIfScoreSorting($criteria)) {
            $criteria->resetSorting();
        }
    }

    /**
     * @param Criteria $criteria
     * @return bool
     */
    protected function checkIfScoreSorting(Criteria $criteria)
    {
        /** @var FieldSorting */
        $sorting = !empty($criteria->getSorting()) ? $criteria->getSorting()[0] : null;

        if ($sorting) {
            $this->isScoreSorting = $sorting->getField() === '_score';
        }

        return $this->isScoreSorting;
    }

    /**
     * @param Criteria $criteria
     */
    protected function addProductNumbersToCriteria(Criteria $criteria): void
    {
        if ($this->isAssocArray($this->doofinderIds)) {

            $criteria->addFilter(
                new OrFilter([
                    new EqualsAnyFilter('productNumber', array_keys($this->doofinderIds)),
                    new EqualsAnyFilter('parent.productNumber', array_values($this->doofinderIds)),
                    new EqualsAnyFilter('productNumber', array_values($this->doofinderIds))
                ])
            );
        }
        else {
            $criteria->addFilter(new EqualsAnyFilter('productNumber', array_values($this->doofinderIds)));
        }
    }

    /**
     * @param array $arr
     * @return bool
     */
    protected function isAssocArray(array $arr)
    {
        if (array() === $arr)
            return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @param SearchPageLoadedEvent $event
     */
    public function onSearchPageLoadedEvent(SearchPageLoadedEvent $event): void
    {
        $event->getPage()->setListing($this->modifyListing($event->getPage()->getListing()));
    }

    /**
     * @param SuggestPageLoadedEvent $event
     */
    public function onSuggestPageLoadedEvent(SuggestPageLoadedEvent $event): void
    {
        $event->getPage()->setSearchResult($this->modifyListing($event->getPage()->getSearchResult()));
    }

    /**
     * @param EntitySearchResult $listing
     * @return object|ProductListingResult
     */
    protected function modifyListing(EntitySearchResult $listing)
    {
        if ($listing && !empty($this->doofinderIds)) {

            // reorder entities if doofinder score sorting
            if ($this->isSuggestCall || $this->isScoreSorting) {
                $this->orderByProductNumberArray($listing->getEntities(), $listing->getContext());
            }

            $newListing = ProductListingResult::createFrom(new EntitySearchResult(
                $listing->getEntity(),
                $listing->getTotal(),
                $this->sliceEntityCollection($listing->getEntities(), $this->shopwareOffset, $this->shopwareLimit),
                $listing->getAggregations(),
                $listing->getCriteria(),
                $listing->getContext()
            ));

            $newListing->setExtensions($listing->getExtensions());
            $this->reintroduceShopwareLimitAndOffset($newListing);

            if ($this->isSuggestCall == false && $listing instanceof ProductListingResult) {

                $newListing->setSorting($listing->getSorting());

                if (method_exists($listing, "getAvailableSortings") && method_exists($newListing, "setAvailableSortings")) {
                    $newListing->setAvailableSortings($listing->getAvailableSortings());
                }
                else if (method_exists($listing, "getSortings") && method_exists($newListing, "setSortings")) {
                    $newListing->setSortings($listing->getSortings());
                }
            }

            return $newListing;
        }

        return $listing;
    }

    /**
     * @param EntityCollection $collection
     * @param Context $context
     * @return EntityCollection
     */
    protected function orderByProductNumberArray(EntityCollection $collection, Context $context): EntityCollection
    {
        if ($collection) {

            $productNumbers = array_keys($this->doofinderIds);
            $groupNumbers   = array_values($this->doofinderIds);
            $parentIds      = $collection->filter(function(ProductEntity $product) { return !!$product->getParentId(); })->map(function(ProductEntity $product) { return $product->getParentId(); });
            $parentNumbers  = $this->getParentNumbers($parentIds, $context);

            $collection->sort(
                function (ProductEntity $a, ProductEntity $b) use ($productNumbers, $groupNumbers, $parentNumbers) {

                    $aIndex = array_search($a->getProductNumber(), $productNumbers);
                    $bIndex = array_search($b->getProductNumber(), $productNumbers);

                    if (count($parentNumbers) > 0) {
                        // order by product number and search parents
                        if (($aIndex === false || $bIndex === false) && ($parentNumbers[$a->getParentId()] || $parentNumbers[$b->getParentId()])) {
                            $aIndex = array_search($parentNumbers[$a->getParentId()], $productNumbers);
                            $bIndex = array_search($parentNumbers[$b->getParentId()], $productNumbers);
                        }

                        // order by group number and search parents
                        if (($aIndex === false || $bIndex === false) && ($parentNumbers[$a->getParentId()] || $parentNumbers[$b->getParentId()])) {
                            $aIndex = array_search($parentNumbers[$a->getParentId()], $groupNumbers);
                            $bIndex = array_search($parentNumbers[$b->getParentId()], $groupNumbers);
                        }
                    }

                    return ($aIndex !== false ? $aIndex : PHP_INT_MAX) - ($bIndex !== false ? $bIndex : PHP_INT_MAX); }
            );
        }

        return $collection;
    }

    /**
     * @param array $parentIds
     * @param Context $context
     * @return array
     */
    protected function getParentNumbers(array $parentIds, Context $context): array
    {
        if (empty($parentIds)) {
            return [];
        }

        $parentNumbers = [];

        /** @var ProductEntity $parent */
        foreach ($this->productRepository->search(new Criteria($parentIds), $context) as $parent) {
            $parentNumbers[$parent->getId()] = $parent->getProductNumber();
        }

        return $parentNumbers;
    }

    /**
     * @param Criteria $criteria
     */
    protected function storeShopwareLimitAndOffset(Criteria $criteria): void
    {
        $this->shopwareLimit  = $criteria->getLimit();
        $this->shopwareOffset = $criteria->getOffset();
    }

    /**
     * @param Criteria $criteria
     */
    protected function manipulateCriteriaLimitAndOffset(Criteria $criteria): void
    {
        $criteria->setLimit(count($this->doofinderIds));
        $criteria->setOffset(0);
    }

    /**
     * @param ProductListingResult $newListing
     */
    protected function reintroduceShopwareLimitAndOffset(ProductListingResult $newListing): void
    {
        $newListing->setLimit($this->shopwareLimit);
        $newListing->getCriteria()->setLimit($this->shopwareLimit);
        $newListing->getCriteria()->setOffset($this->shopwareOffset);
    }

    /**
     * @param EntityCollection $collection
     * @param $offset
     * @param $limit
     * @return EntityCollection
     */
    protected function sliceEntityCollection(EntityCollection $collection, $offset, $limit): EntityCollection
    {
        $iterator    = $collection->getIterator();
        $newEntities = [];
        $i = 0;

        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {

            if ($i >= $offset && $i < $offset + $limit) {
                $newEntities[] = $iterator->current();
            }

            $i++;
        }

        return new EntityCollection($newEntities);
    }

    /**
     * @param SalesChannelContext $context
     * @return array|bool|float|int|string|null
     */
    protected function getDoofinderSearchSubscriberActivationMode(SalesChannelContext $context)
    {
        $doofinderSearchSubscriberActivate = $this->systemConfigService->get(
            'IntediaDoofinderSW6.config.doofinderSearchSubscriberActivate',
            $context ? $context->getSalesChannel()->getId() : null
        );
        return $doofinderSearchSubscriberActivate;
    }
}
