<?php declare(strict_types=1);

namespace IronMatomo\Subscriber;

use IronMatomo\Matomo\MatomoDataService;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MatomoSettingsSubscriber
 *
 * @package IronMatomo\Subscriber
 */
class MatomoSettingsSubscriber implements EventSubscriberInterface
{
    const MATOMO_DATA_EXTENSION_ID = 'ironMatomoData';
    const MATOMO_DATA_RUNTIME_ID = 'ironMatomoExt';

    /**
     * @var MatomoDataService
     */
    private MatomoDataService $matomoDataService;

    /**
     * @var EntityRepository
     */
    private EntityRepository $productRepository;

    /**
     * @var CategoryBreadcrumbBuilder
     */
    private CategoryBreadcrumbBuilder $breadcrumbBuilder;

    /**
     * MatomoSettingsSubscriber constructor.
     *
     * @param MatomoDataService $matomoDataService
     * @param EntityRepository $productRepository
     * @param CategoryBreadcrumbBuilder $breadcrumbBuilder
     */
    public function __construct(
        MatomoDataService $matomoDataService,
        EntityRepository $productRepository,
        CategoryBreadcrumbBuilder $breadcrumbBuilder
    )
    {
        $this->matomoDataService = $matomoDataService;
        $this->productRepository = $productRepository;
        $this->breadcrumbBuilder = $breadcrumbBuilder;
    }

    /*
     * @inherit
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender',
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
            CheckoutCartPageLoadedEvent::class => 'onCheckoutCartPageLoaded',
            CheckoutConfirmPageLoadedEvent::class => 'onCheckoutConfirmPageLoaded',
            CheckoutFinishPageLoadedEvent::class => 'onCheckoutFinishPageLoaded',
        ];
    }

    /**
     * @param StorefrontRenderEvent $event
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function onStorefrontRender(StorefrontRenderEvent $event)
    {
        if ($event->getRequest()->isXmlHttpRequest()) {
            return;
        }
        $parameters = $event->getParameters();
        if (isset($parameters['page'])) {
            $matomoData = $this->matomoDataService->getMatomoData($event->getSalesChannelContext());
            $page = $parameters['page'];
            // Absicherung, weil jemand das Objekt $page zerschießt und ein array daraus macht.
            // https://account.shopware.com/producer/support/164186
            if (is_array($page)) {
                $page['extension'][self::MATOMO_DATA_EXTENSION_ID] = $matomoData;
                return;
            }
            $page->addExtensions([self::MATOMO_DATA_EXTENSION_ID => $matomoData]);
        }
    }

    /**
     * @param ProductPageLoadedEvent $event
     * @return void
     */
    public function onProductPageLoaded(ProductPageLoadedEvent $event)
    {
        $salesChannelContext = $event->getSalesChannelContext();
        $categoryEntity = $this->breadcrumbBuilder->getProductSeoCategory($event->getPage()->getProduct(), $salesChannelContext);
        $event->getPage()->addArrayExtension(self::MATOMO_DATA_RUNTIME_ID, [
            'seoCategory' => $categoryEntity ? $categoryEntity->getTranslation('name') : null,
        ]);
    }

    /**
     * @param CheckoutCartPageLoadedEvent $event
     * @return void
     */
    public function onCheckoutCartPageLoaded(CheckoutCartPageLoadedEvent $event): void
    {
        $lineItems = $event->getPage()->getCart()->getLineItems();
        $cardData = $this->getDataForEachLineItem($lineItems, $event->getSalesChannelContext());
        $event->getPage()->addArrayExtension(self::MATOMO_DATA_RUNTIME_ID, [
            'seoCategory' => $cardData,
        ]);
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     * @return void
     */
    public function onCheckoutConfirmPageLoaded(CheckoutConfirmPageLoadedEvent $event): void
    {
        $lineItems = $event->getPage()->getCart()->getLineItems();
        $cardData = $this->getDataForEachLineItem($lineItems, $event->getSalesChannelContext());
        $event->getPage()->addArrayExtension(self::MATOMO_DATA_RUNTIME_ID, [
            'seoCategory' => $cardData,
        ]);
    }

    /**
     * @param CheckoutFinishPageLoadedEvent $event
     * @return void
     */
    public function onCheckoutFinishPageLoaded(CheckoutFinishPageLoadedEvent $event): void
    {
        $lineItems = $event->getPage()->getOrder()->getLineItems();
        $cardData = $this->getDataForEachLineItem($lineItems, $event->getSalesChannelContext());
        $event->getPage()->addArrayExtension(self::MATOMO_DATA_RUNTIME_ID, [
            'seoCategory' => $cardData,
        ]);
    }

    /**
     * @param OrderLineItemCollection|LineItemCollection|null $lineItems
     * @param SalesChannelContext $salesChannelContext
     * @return array
     */
    private function getDataForEachLineItem($lineItems, SalesChannelContext $salesChannelContext): array
    {
        if ($lineItems === null) {
            return [];
        }

        $cardData = [];
        /** @var OrderLineItemEntity|LineItem $item */
        foreach ($lineItems as $item)
        {
            $payload = $item->getPayload();
            try {
                if (!isset($payload['productNumber'])) {
                    continue;
                }
                $productNumber = $payload['productNumber'];
                $cardData[ $productNumber ] =  $this->findCategoryNameByProductNumber($productNumber, $salesChannelContext);
            } catch (CategoryNotFoundException $exception) {
                // Handle category not found exception
            }
        }
        return $cardData;
    }

    /**
     * @param string $productNumber
     * @param SalesChannelContext $salesChannelContext
     * @return string|null
     */
    private function findCategoryNameByProductNumber(string $productNumber, SalesChannelContext $salesChannelContext): ?string
    {
        $context = $salesChannelContext->getContext();
        try
        {
            $product = $this->productRepository->search(
                (new Criteria())
                    ->addFilter(new EqualsFilter('productNumber', $productNumber)),
                $context
            )->first();

            $category = null;
            if ($product) {
                $category = $this->breadcrumbBuilder->getProductSeoCategory($product, $salesChannelContext);
            }

        } catch (\Exception $exception) {
            $category = null;
        }
        return $category ? $category->getTranslation('name') : null;
    }
}
