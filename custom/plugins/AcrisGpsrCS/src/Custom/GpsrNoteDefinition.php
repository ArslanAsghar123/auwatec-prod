<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrNote\GpsrNoteRuleDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrNote\GpsrNoteSalesChannelDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrNote\GpsrNoteStreamDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrNoteTranslation\GpsrNoteTranslationDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class GpsrNoteDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'acris_gpsr_note';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return GpsrNoteCollection::class;
    }

    public function getEntityClass(): string
    {
        return GpsrNoteEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new StringField('internal_id', 'internalId'))->addFlags(new ApiAware()),
            (new TranslatedField('internalName'))->addFlags(new Required()),
            (new TranslatedField('internalNotice'))->addFlags(new Required()),
            (new StringField('note_type', 'noteType'))->addFlags(new ApiAware()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware()),

            (new TranslatedField('headline'))->addFlags(new Required()),
            (new TranslatedField('text'))->addFlags(new Required()),
            (new StringField('display_type', 'displayType'))->addFlags(new ApiAware()),
            (new StringField('tab_position', 'tabPosition'))->addFlags(new ApiAware()),
            (new TranslatedField('modalInfoText'))->addFlags(new Required()),
            (new TranslatedField('modalLinkText'))->addFlags(new Required()),
            (new StringField('description_display', 'descriptionDisplay'))->addFlags(new ApiAware()),
            (new StringField('description_position', 'descriptionPosition'))->addFlags(new ApiAware()),
            (new StringField('display_separator', 'displaySeparator'))->addFlags(new ApiAware()),

            (new StringField('background_color', 'backgroundColor'))->addFlags(new ApiAware()),
            (new StringField('border_color', 'borderColor'))->addFlags(new ApiAware()),
            (new StringField('headline_color', 'headlineColor'))->addFlags(new ApiAware()),

            (new StringField('hint_headline_seo_size', 'hintHeadlineSeoSize'))->addFlags(new ApiAware()),
            (new StringField('hint_alignment', 'hintAlignment'))->addFlags(new ApiAware()),
            (new StringField('hint_headline_color', 'hintHeadlineColor'))->addFlags(new ApiAware()),
            (new BoolField('hint_enable_headline_size', 'hintEnableHeadlineSize'))->addFlags(new ApiAware()),
            (new StringField('hint_headline_size', 'hintHeadlineSize'))->addFlags(new ApiAware()),

            (new StringField('media_position', 'mediaPosition'))->addFlags(new ApiAware()),
            (new IntField('media_size', 'mediaSize'))->addFlags(new ApiAware()),
            (new StringField('mobile_visibility', 'mobileVisibility'))->addFlags(new ApiAware()),

            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', false))->addFlags(new ApiAware()),

            (new ManyToManyAssociationField('productStreams', ProductStreamDefinition::class, GpsrNoteStreamDefinition::class, 'gpsr_note_id', 'product_stream_id'))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField('salesChannels', SalesChannelDefinition::class, GpsrNoteSalesChannelDefinition::class, 'gpsr_note_id', 'sales_channel_id'))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField("rules", RuleDefinition::class, GpsrNoteRuleDefinition::class, "gpsr_note_id", "rule_id"))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(GpsrNoteTranslationDefinition::class, 'acris_gpsr_note_id'))->addFlags(new ApiAware()),
        ]);
    }
}
