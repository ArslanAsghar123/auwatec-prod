<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom;

use Acris\DiscountGroup\Custom\Aggregate\DiscountGroupRule\DiscountGroupRuleDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RuleAreas;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RuleExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'acrisDiscountGroups',
                DiscountGroupDefinition::class,
                DiscountGroupRuleDefinition::class,
                'rule_id',
                'discount_group_id'
            ))->addFlags(new RuleAreas(RuleAreas::PRODUCT_AREA))
        );
    }

    public function getDefinitionClass(): string
    {
        return RuleDefinition::class;
    }
}
