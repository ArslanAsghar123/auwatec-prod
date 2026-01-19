<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrNoteTranslation;

use Acris\Gpsr\Custom\GpsrNoteDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class GpsrNoteTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'acris_gpsr_note_translation';
    }

    public function getCollectionClass(): string
    {
        return GpsrNoteTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return GpsrNoteTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return GpsrNoteDefinition::class;
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
            (new CustomFields())->addFlags(new ApiAware())
        ]);
    }
}
