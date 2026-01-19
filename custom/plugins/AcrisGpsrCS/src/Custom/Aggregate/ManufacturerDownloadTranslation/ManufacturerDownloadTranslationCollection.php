<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\ManufacturerDownloadTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(ManufacturerDownloadTranslationEntity $entity)
 * @method void                         set(string $key, ManufacturerDownloadTranslationEntity $entity)
 * @method ManufacturerDownloadTranslationEntity[]    getIterator()
 * @method ManufacturerDownloadTranslationEntity[]    getElements()
 * @method ManufacturerDownloadTranslationEntity|null get(string $key)
 * @method ManufacturerDownloadTranslationEntity|null getAt(int $position)
 * @method ManufacturerDownloadTranslationEntity|null first()
 * @method ManufacturerDownloadTranslationEntity|null last()
 */
class ManufacturerDownloadTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ManufacturerDownloadTranslationEntity::class;
    }
}
