<?php declare(strict_types=1);

namespace Mill\ProductDownloadsTab\Subscriber;

use Mill\ProductDownloadsTab\Service\ProductDownloadsService;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PageSubscriber
 *
 * @package Mill\ProductDownloadsTab\Subscriber
 */

class PageSubscriber implements EventSubscriberInterface
{

    /**
     * @var SystemConfigService
     */
    private SystemConfigService $systemConfigService;

    /**
     * @var ProductDownloadsService
     */
    private ProductDownloadsService $productDownloadsService;

    /**
     * @param SystemConfigService $systemConfigService
     * @param ProductDownloadsService $productDownloadsService
     */

    public function __construct(
        SystemConfigService $systemConfigService,
        ProductDownloadsService $productDownloadsService
    )
    {

        $this->systemConfigService = $systemConfigService;
        $this->productDownloadsService = $productDownloadsService;

    }

    /**
     * {@inheritDoc}
     */

    public static function getSubscribedEvents(): array
    {

        return [
            ProductPageLoadedEvent::class => ['onProductPageLoaded', 100]
        ];

    }

    /**
     * Event-function to extend the product page with product advantages
     *
     * @param ProductPageLoadedEvent $event
     * @throws InconsistentCriteriaIdsException
     */

    public function onProductPageLoaded(ProductPageLoadedEvent $event)
    {

        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();

        if (!(bool) $this->systemConfigService->get('MillProductDownloadsTab.config.active', $salesChannelId)) {

            return;

        }

        $page = $event->getPage();

        $product = $page->getProduct();

        $mediaFiles = [];

        if (!empty($product)) {

            $mediaFiles = $this->productDownloadsService->getProductDownloads($event->getSalesChannelContext()->getContext(), $product);

        }

        $page->assign(
            [
                'MillProductDownloadsTab' => [
                    'files' => $mediaFiles
                ]
            ]
        );

    }

}