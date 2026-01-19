<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrManufacturer;

use Acris\Gpsr\Custom\GpsrManufacturerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class GpsrManufacturerSalesChannelDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'acris_gpsr_mf_sales_channel';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('gpsr_manufacturer_id', 'gpsrManufacturerId', GpsrManufacturerDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('gpsrManufacturer', 'gpsr_manufacturer_id', GpsrManufacturerDefinition::class),
            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class),
            new CreatedAtField()
        ]);
    }
}
