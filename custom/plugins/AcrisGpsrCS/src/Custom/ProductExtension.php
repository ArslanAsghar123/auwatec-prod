<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'acrisGpsrDownloads',
                ProductGpsrDownloadDefinition::class,
                'product_id',
                'id')
            )->addFlags(new CascadeDelete(), new Inherited(), new ApiAware())
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
