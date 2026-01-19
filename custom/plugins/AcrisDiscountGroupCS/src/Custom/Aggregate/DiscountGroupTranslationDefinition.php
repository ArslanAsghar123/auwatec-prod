<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom\Aggregate;

use Acris\DiscountGroup\Custom\DiscountGroupDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class DiscountGroupTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'acris_discount_group_translation';
    }

    public function getCollectionClass(): string
    {
        return DiscountGroupTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return DiscountGroupTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return DiscountGroupDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new LongTextField('display_text', 'displayText'))->addFlags(new AllowHtml(), new ApiAware()),
            (new LongTextField('display_name', 'displayName'))->addFlags(new ApiAware())
        ]);
    }
}
