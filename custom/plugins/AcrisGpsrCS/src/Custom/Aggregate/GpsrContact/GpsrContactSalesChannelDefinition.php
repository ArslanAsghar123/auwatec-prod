<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrContact;

use Acris\Gpsr\Custom\GpsrContactDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class GpsrContactSalesChannelDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'acris_gpsr_contact_sales_channel';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('gpsr_contact_id', 'gpsrContactId', GpsrContactDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('gpsrContact', 'gpsr_contact_id', GpsrContactDefinition::class),
            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class),
            new CreatedAtField()
        ]);
    }
}
