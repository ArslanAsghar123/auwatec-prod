<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrContactTranslation;

use Acris\Gpsr\Custom\GpsrContactDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class GpsrContactTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'acris_gpsr_contact_translation';
    }

    public function getCollectionClass(): string
    {
        return GpsrContactTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return GpsrContactTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return GpsrContactDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('internal_name', 'internalName'))->addFlags(new ApiAware()),
            (new StringField('internal_notice', 'internalNotice'))->addFlags(new ApiAware()),
            (new StringField('headline', 'headline'))->addFlags(new ApiAware()),
            (new LongTextField('text', 'text'))->addFlags(new AllowHtml(), new ApiAware()),
            (new LongTextField('modal_info_text', 'modalInfoText'))->addFlags(new AllowHtml(), new ApiAware()),
            (new StringField('modal_link_text', 'modalLinkText'))->addFlags(new ApiAware()),

            (new StringField('name', 'name'))->addFlags(new ApiAware()),
            (new StringField('street', 'street'))->addFlags(new ApiAware()),
            (new StringField('house_number', 'houseNumber'))->addFlags(new ApiAware()),
            (new StringField('zipcode', 'zipcode'))->addFlags(new ApiAware()),
            (new StringField('city', 'city'))->addFlags(new ApiAware()),
            (new StringField('country', 'country'))->addFlags(new ApiAware()),
            (new StringField('phone_number', 'phoneNumber'))->addFlags(new ApiAware()),
            (new StringField('address', 'address'))->addFlags(new ApiAware()),

            (new CustomFields())->addFlags(new ApiAware())
        ]);
    }
}
