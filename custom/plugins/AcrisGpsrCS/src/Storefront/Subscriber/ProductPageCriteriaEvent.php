<?php

namespace Acris\Gpsr\Storefront\Subscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPageCriteriaEvent implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            \Shopware\Storefront\Page\Product\ProductPageCriteriaEvent::class => ['productPageCriteriaEvent']
        ];
    }

    public function productPageCriteriaEvent(\Shopware\Storefront\Page\Product\ProductPageCriteriaEvent $event)
    {
       $criteria = $event->getCriteria();
       $criteria->addAssociation("acrisGpsrDownloads");
        $criteria->addAssociation("manufacturer.acrisManufacturerDownloads");

    }
}