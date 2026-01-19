<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom\Aggregate\DiscountGroupProductStream;

use Acris\DiscountGroup\Custom\DiscountGroupDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class DiscountGroupProductStreamDefinition extends MappingEntityDefinition
{
    public function getEntityName(): string
    {
        return 'acris_discount_dynamic_groups';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('discount_group_id', 'discountGroupId', DiscountGroupDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new ReferenceVersionField(DiscountGroupDefinition::class))->addFlags(new Required()),
            (new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('discountGroup', 'discount_group_id', DiscountGroupDefinition::class),
            new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class),
            new CreatedAtField(),
        ]);
    }
}
