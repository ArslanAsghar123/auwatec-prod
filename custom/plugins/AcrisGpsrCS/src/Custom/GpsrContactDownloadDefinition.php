<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrContactDownloadTranslation\GpsrContactDownloadTranslationDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class GpsrContactDownloadDefinition extends EntityDefinition
{
    public CONST ENTITY_NAME = 'acris_gpsr_c_d';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }
    public function getCollectionClass(): string
    {
        return ProductGpsrDownloadCollection::class;
    }
    public function getEntityClass(): string
    {
        return ProductGpsrDownloadEntity::class;
    }
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new FKField('preview_media_id', 'previewMediaId', MediaDefinition::class))->addFlags(new ApiAware()),
            (new TranslatedField('fileName'))->addFlags(new ApiAware()),
            (new FkField('acris_gpsr_contact_id', 'acrisGpsrContactId', GpsrContactDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('previewMedia', 'preview_media_id', MediaDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('contacts', 'acrisGpsrContactId', GpsrContactDefinition::class, 'id'))->addFlags(new ApiAware()),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),
            (new BoolField('preview_image_enabled', 'previewImageEnabled'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(GpsrContactDownloadTranslationDefinition::class, 'acris_gpsr_c_d_id'))->addFlags(new ApiAware()),
        ]);
    }
}
