<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrContact\GpsrContactStreamDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrManufacturer\GpsrManufacturerStreamDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrNote\GpsrNoteStreamDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductStreamExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrNotes',
                GpsrNoteDefinition::class,
                GpsrNoteStreamDefinition::class,
                'product_stream_id',
                'gpsr_note_id'
            ))->addFlags(new Inherited(), new ApiAware())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrManufacturers',
                GpsrManufacturerDefinition::class,
                GpsrManufacturerStreamDefinition::class,
                'product_stream_id',
                'gpsr_manufacturer_id'
            ))->addFlags(new Inherited(), new ApiAware())
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'acrisGpsrContacts',
                GpsrContactDefinition::class,
                GpsrContactStreamDefinition::class,
                'product_stream_id',
                'gpsr_contact_id'
            ))->addFlags(new Inherited(), new ApiAware())
        );
    }
    public function getDefinitionClass(): string
    {
        return ProductStreamDefinition::class;
    }
}
