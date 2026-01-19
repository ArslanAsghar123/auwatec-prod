<?php

namespace Acris\Gpsr\Storefront\Subscriber;
use Acris\Gpsr\Components\ProductGpsrInfo\GpsrService;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductExportSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly GpsrService $gpsrService)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            'sales_channel.product.loaded' => 'onSalesChannelProductLoaded',
        ];
    }

    public function onSalesChannelProductLoaded(SalesChannelEntityLoadedEvent $event): void
    {
        if($event->getContext()->hasExtension(ProductCriteriaSubscriber::PRODUCT_ALLOW_GPSR_DATA_CONTEXT_EXTENSION) === true) {
            /** @var SalesChannelProductEntity $product */
            foreach ($event->getEntities() as $product) {
                $this->gpsrService->loadGpsr($product, $event->getSalesChannelContext());
            }
        }
    }
}
