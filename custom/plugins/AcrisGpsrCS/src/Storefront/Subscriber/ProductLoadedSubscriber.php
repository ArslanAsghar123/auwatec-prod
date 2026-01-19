<?php declare(strict_types=1);

namespace Acris\Gpsr\Storefront\Subscriber;

use Acris\Gpsr\Components\ProductGpsrInfo\GpsrService;
use Acris\Gpsr\Components\ProductGpsrInfo\Struct\GpsrMasterStruct;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductLoadedSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly GpsrService $gpsrService)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            /* With 30 we come after 50 (Plugin EmcgnNoVariantPreselection changes product inside event) */
            ProductPageLoadedEvent::class => ['productLoaded', 30]
        ];
    }

    public function productLoaded(ProductPageLoadedEvent $event): void
    {
        $this->gpsrService->loadGpsr($event->getPage()->getProduct(), $event->getSalesChannelContext());
    }
}
