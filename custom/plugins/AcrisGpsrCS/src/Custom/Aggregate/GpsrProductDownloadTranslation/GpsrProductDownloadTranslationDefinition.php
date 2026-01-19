<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrProductDownloadTranslation;

use Acris\Gpsr\Custom\ProductGpsrDownloadDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class GpsrProductDownloadTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'acris_gpsr_p_d_translation';
    }

    public function getCollectionClass(): string
    {
        return GpsrProductDownloadTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return GpsrProductDownloadTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return ProductGpsrDownloadDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('file_name', 'fileName'))->addFlags(new ApiAware()),
            (new CustomFields())->addFlags(new ApiAware())
        ]);
    }
}
