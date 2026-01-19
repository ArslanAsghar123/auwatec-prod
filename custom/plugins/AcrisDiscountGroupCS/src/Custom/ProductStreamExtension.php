<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom;

use Acris\DiscountGroup\Custom\Aggregate\DiscountGroupProductStream\DiscountGroupProductStreamDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductStreamExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'acrisDiscountGroups',
                DiscountGroupDefinition::class,
                DiscountGroupProductStreamDefinition::class,
                'product_stream_id',
                'discount_group_id'
            ))
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductStreamDefinition::class;
    }
}
