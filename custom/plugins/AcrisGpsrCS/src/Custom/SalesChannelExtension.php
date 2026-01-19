<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrContact\GpsrContactSalesChannelDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrManufacturer\GpsrManufacturerSalesChannelDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrNote\GpsrNoteSalesChannelDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class SalesChannelExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrNotes',
                GpsrNoteDefinition::class,
                GpsrNoteSalesChannelDefinition::class,
                'sales_channel_id',
                'gpsr_note_id'
            ))->addFlags(new ApiAware())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrManufacturers',
                GpsrManufacturerDefinition::class,
                GpsrManufacturerSalesChannelDefinition::class,
                'sales_channel_id',
                'gpsr_manufacturer_id'
            ))->addFlags(new ApiAware())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrContacts',
                GpsrContactDefinition::class,
                GpsrContactSalesChannelDefinition::class,
                'sales_channel_id',
                'gpsr_contact_id'
            ))->addFlags(new ApiAware())
        );
    }

    public function getDefinitionClass(): string
    {
        return SalesChannelDefinition::class;
    }
}
