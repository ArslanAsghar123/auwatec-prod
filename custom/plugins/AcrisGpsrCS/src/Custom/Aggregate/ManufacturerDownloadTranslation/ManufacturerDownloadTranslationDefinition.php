<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\ManufacturerDownloadTranslation;

use Acris\Gpsr\Custom\ManufacturerDownloadDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ManufacturerDownloadTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'acris_mf_d_translation';
    }

    public function getCollectionClass(): string
    {
        return ManufacturerDownloadTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ManufacturerDownloadTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return ManufacturerDownloadDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('file_name', 'fileName'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware())
        ]);
    }
}
