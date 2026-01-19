<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrContactDownloadTranslation;

use Acris\Gpsr\Custom\GpsrContactDownloadDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class GpsrContactDownloadTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'acris_gpsr_c_d_translation';
    }

    public function getCollectionClass(): string
    {
        return GpsrContactDownloadTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return GpsrContactDownloadTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return GpsrContactDownloadDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('file_name', 'fileName'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware())
        ]);
    }
}
