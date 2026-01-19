<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrNoteDownloadTranslation;

use Acris\Gpsr\Custom\GpsrNoteDownloadDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class GpsrNoteDownloadTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'acris_gpsr_n_d_translation';
    }

    public function getCollectionClass(): string
    {
        return GpsrNoteDownloadTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return GpsrNoteDownloadTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return GpsrNoteDownloadDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('file_name', 'fileName'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware())
        ]);
    }
}
