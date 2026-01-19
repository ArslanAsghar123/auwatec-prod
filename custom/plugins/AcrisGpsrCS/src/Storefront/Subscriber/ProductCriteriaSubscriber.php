<?php declare(strict_types=1);

namespace Acris\Gpsr\Storefront\Subscriber;

use Acris\Gpsr\Components\ProductGpsrInfo\Struct\GpsrMasterStruct;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductCriteriaSubscriber implements EventSubscriberInterface
{
    public const PRODUCT_ALLOW_GPSR_DATA_CRITERIA_TITLE = ['product-export::products'];
    public const PRODUCT_ALLOW_GPSR_DATA_CONTEXT_EXTENSION = 'acrisAllowLoadGpsrData';

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.product.process.criteria' => 'onProductCriteriaLoaded'
        ];
    }

    public function onProductCriteriaLoaded(SalesChannelProcessCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();
        if($criteria->hasAssociation('streams') === false) {
            $criteria->addAssociation('streams');
        }

        // set mark to load gpsr data
        if(in_array($criteria->getTitle(), self::PRODUCT_ALLOW_GPSR_DATA_CRITERIA_TITLE)) {
            $event->getContext()->addExtension(self::PRODUCT_ALLOW_GPSR_DATA_CONTEXT_EXTENSION, new GpsrMasterStruct());
        }
    }
}
