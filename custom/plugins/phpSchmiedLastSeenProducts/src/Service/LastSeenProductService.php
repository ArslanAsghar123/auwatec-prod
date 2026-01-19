<?php declare(strict_types=1);

namespace phpSchmied\LastSeenProducts\Service;

use DateTime;
use phpSchmied\LastSeenProducts\phpSchmiedLastSeenProducts;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LastSeenProductService
{
    private SalesChannelRepository $salesChannelProductRepository;

    private SystemConfigService $systemConfigService;

    private EntityRepository $customerAlsoSeenRepository;

    /**
     * @param SalesChannelRepository $salesChannelProductRepository
     * @param SystemConfigService $systemConfigService
     * @param EntityRepository $customerAlsoSeenRepository
     */
    public function __construct(
        SalesChannelRepository $salesChannelProductRepository,
        SystemConfigService    $systemConfigService,
        EntityRepository       $customerAlsoSeenRepository
    )
    {
        $this->salesChannelProductRepository = $salesChannelProductRepository;
        $this->systemConfigService = $systemConfigService;
        $this->customerAlsoSeenRepository = $customerAlsoSeenRepository;
    }

    private function getLastSeenProductIdsArr(SessionInterface $session, SalesChannelContext $context, string $currentProductId = ''): array
    {
        $lastSeenProducts = $session->get('lastSeenProductIds');

        if (empty($lastSeenProducts)) {
            return [];
        }

        if ($context->getCustomer() instanceof CustomerEntity) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('customerId', $context->getCustomer()->getId()));
            $criteria->addSorting(new FieldSorting('lastView', FieldSorting::DESCENDING));
            $customerSeenProductsArr = $this->customerAlsoSeenRepository->searchIds($criteria, $context->getContext())->getIds();

            $customerSeenProducts = [];
            foreach ($customerSeenProductsArr as $product) {
                $customerSeenProducts[] = $product['productId'];
            }

            $lastSeenProducts = array_merge($lastSeenProducts, $customerSeenProducts);

            if (count($lastSeenProducts) > $this->getPluginConfig($context->getSalesChannel()->getId())['limit']) {
                $lastSeenProducts = array_slice($lastSeenProducts, 0, $this->getPluginConfig($context->getSalesChannel()->getId())['limit']);
            }
        }

        if (in_array($currentProductId, $lastSeenProducts)) {
            $lastSeenProducts = array_diff($lastSeenProducts, [$currentProductId]);
        }

        return $lastSeenProducts;
    }

    /**
     * @param string $currentProductId
     * @param SessionInterface $session
     * @param SalesChannelContext $salesChannelContext
     *
     * @return EntityCollection
     */
    public function getLastSeenProductCollection(
        SessionInterface    $session,
        SalesChannelContext $salesChannelContext,
        string              $currentProductId = ''
    ): EntityCollection
    {
        $lastSeenProductIdsArr = $this->getLastSeenProductIdsArr($session, $salesChannelContext, $currentProductId);

        if (empty($lastSeenProductIdsArr)) {
            return new ProductCollection();
        }

        if (count($lastSeenProductIdsArr) > 0) {
            $salesChannelProductCollection = $this->buildProductCollectionFromIds($lastSeenProductIdsArr, $salesChannelContext);
        }

        return $salesChannelProductCollection ?? new ProductCollection();
    }

    /**
     * @param string $productIdToAdd
     * @param SessionInterface $session
     * @param SalesChannelContext $context
     * @return array
     */
    public function addLastSeenProductId(string $productIdToAdd, SessionInterface $session, SalesChannelContext $context): array
    {
        $arr = $this->getLastSeenProductIdsArr($session, $context, $productIdToAdd);

        if ($this->getPluginConfig($context->getSalesChannel()->getId())['variants']) {
            $arr = $this->checkVariants($arr, $productIdToAdd, $context);
        }

        array_unshift($arr, $productIdToAdd);

        if ($context->getCustomer() instanceof CustomerEntity && count($arr) > $this->getPluginConfig($context->getSalesChannel()->getId())['limit']) {
            $lastElem = array_pop($arr);

            $this->customerAlsoSeenRepository->delete([
                [
                    'customerId' => $context->getCustomer()->getId(),
                    'productId' => $lastElem,
                ],
            ], $context->getContext());
        }

        if ($context->getCustomer() instanceof CustomerEntity && $this->getPluginConfig($context->getSalesChannel()->getId())['saveCustomer']) {
            $this->customerAlsoSeenRepository->upsert([
                [
                    'customerId' => $context->getCustomer()->getId(),
                    'productId' => $productIdToAdd,
                    'lastView' => new DateTime()
                ],
            ], $context->getContext());
        }

        return $arr;
    }

    private function buildProductCollectionFromIds(
        array               $lastSeenProductIdsArr,
        SalesChannelContext $salesChannelContext
    ): EntityCollection
    {

        $productCollection = new ProductCollection();
        foreach ($lastSeenProductIdsArr as $productId) {
            $criteria = new Criteria();
            $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('product.id', $productId),
                new RangeFilter('product.visibilities.visibility', [RangeFilter::GTE => 10]),
                new EqualsFilter('product.visibilities.salesChannelId',
                    $salesChannelContext->getSalesChannel()->getId()),
            ]));

            if (isset($config['inactive']) && $config['inactive'] === true) {
                $criteria->addFilter(new EqualsFilter('product.active', true));
            }

            if (isset($config['abverkauf']) && $config['abverkauf'] === true) {
                $criteria->addFilter(new RangeFilter('product.stock', [RangeFilter::GT => 0]));
            }

            $criteria->addAssociation('cover');
            $criteria->addAssociation('options.group');

            $product = $this->salesChannelProductRepository->search($criteria, $salesChannelContext)->first();
            $config = $this->getPluginConfig($salesChannelContext->getSalesChannel()->getId());

            if (
                isset($config['categories']) &&
                is_array($config['categories']) &&
                count($config['categories'])
            ) {
                foreach ($config['categories'] as $category_id) {
                    if (in_array($category_id, $product->getCategoryTree())) {
                        continue 2;
                    }
                }
            }

            /**@var $crossSellProduct ProductEntity* */
            if (
                isset($config['disabledCategories']) &&
                is_array($config['disabledCategories']) &&
                count($config['disabledCategories'])
            ) {
                foreach ($config['disabledCategories'] as $category_id) {
                    if (in_array($category_id, $product->getCategoryTree())) {
                        continue 2;
                    }
                }
            }

            if ($product) {
                $productCollection->add($product);
            }
        }

        return $productCollection;
    }

    private function getPluginConfig(string $salesChannelId): array
    {
        return $this->systemConfigService->get(phpSchmiedLastSeenProducts::MODUL_NAME, $salesChannelId)['config'];
    }

    private function checkVariants(array $lastSeenProducts, string $addProduct, SalesChannelContext $context): array
    {
        $product = $this->salesChannelProductRepository->search(new Criteria([$addProduct]), $context)->first();

        if ($product->getParentId() !== null) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('parentId', $product->getParentId()));
            $variants = $this->salesChannelProductRepository->searchIds($criteria, $context)->getIds();

            foreach ($variants as $variant) {
                if (in_array($variant, $lastSeenProducts)) {
                    $lastSeenProducts = array_diff($lastSeenProducts, [$variant]);
                }
            }
        }

        return $lastSeenProducts;
    }
}
