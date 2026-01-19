<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;

class CoreManufacuterExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'acrisManufacturerDownloads',
                ManufacturerDownloadDefinition::class,
                'acris_manufacturer_id',
                'id')
            )->addFlags(new CascadeDelete(), new ApiAware())
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductManufacturerDefinition::class;
    }
}
