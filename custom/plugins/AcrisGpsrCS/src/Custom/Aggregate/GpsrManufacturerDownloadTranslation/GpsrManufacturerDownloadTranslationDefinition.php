<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrManufacturerDownloadTranslation;

use Acris\Gpsr\Custom\GpsrManufacturerDownloadDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class GpsrManufacturerDownloadTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'acris_gpsr_mf_d_translation';
    }

    public function getCollectionClass(): string
    {
        return GpsrManufacturerDownloadTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return GpsrManufacturerDownloadTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return GpsrManufacturerDownloadDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('file_name', 'fileName'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware())
        ]);
    }
}
