<?php declare(strict_types=1);

namespace Acris\Gpsr\Storefront\Subscriber;

use Acris\Configurator\Custom\ConfiguratorDefinition;
use Acris\Configurator\Custom\ConfiguratorEntity;
use Acris\Configurator\Custom\ConfiguratorStepDefinition;
use Acris\Configurator\Custom\ConfiguratorStepEntity;
use Acris\Configurator\Custom\ConfiguratorStepFieldDefinition;
use Acris\Configurator\Custom\ConfiguratorStepFieldRestrictionDefinition;
use Acris\Configurator\Custom\ConfiguratorStepFieldRestrictionEntity;
use Acris\Configurator\Custom\ConfiguratorStepFieldValueDefinition;
use Acris\Gpsr\Custom\GpsrContactDefinition;
use Acris\Gpsr\Custom\GpsrManufacturerDefinition;
use Acris\Gpsr\Custom\GpsrNoteDefinition;
use Acris\Gpsr\Custom\GpsrNoteEntity;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\Util\ConfigReader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[Package('storefront')]
class GpsrSubscriber implements EventSubscriberInterface
{
    public const PRODUCT_CACHE_TAG = 'config.core.listing.hideCloseoutProductsWhenOutOfStock';
    public const GPSR_NOTE_WRITTEN = GpsrNoteDefinition::ENTITY_NAME.'.written';
    public const GPSR_MANUFACTURER_WRITTEN = GpsrManufacturerDefinition::ENTITY_NAME.'.written';
    public const GPSR_CONTACT_WRITTEN = GpsrContactDefinition::ENTITY_NAME.'.written';
    public const MANUFACTURER_WRITTEN = ProductManufacturerDefinition::ENTITY_NAME.'.written';
    public const PRODUCT_WRITTEN = ProductDefinition::ENTITY_NAME.'.written';

    public function __construct(
        private readonly CacheInvalidator $invalidator)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            self::GPSR_NOTE_WRITTEN => 'onGpsrWritten',
            self::GPSR_MANUFACTURER_WRITTEN => 'onGpsrWritten',
            self::GPSR_CONTACT_WRITTEN => 'onGpsrWritten',
            self::MANUFACTURER_WRITTEN => 'onGpsrWritten',
            self::PRODUCT_WRITTEN => 'onGpsrWritten'
        ];
    }
    public function onGpsrWritten(EntityWrittenEvent $event): void
    {
        $this->invalidator->invalidate([self::PRODUCT_CACHE_TAG]);
    }

}
