<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrManufacturerDownloadTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(GpsrManufacturerDownloadTranslationEntity $entity)
 * @method void                         set(string $key, GpsrManufacturerDownloadTranslationEntity $entity)
 * @method GpsrManufacturerDownloadTranslationEntity[]    getIterator()
 * @method GpsrManufacturerDownloadTranslationEntity[]    getElements()
 * @method GpsrManufacturerDownloadTranslationEntity|null get(string $key)
 * @method GpsrManufacturerDownloadTranslationEntity|null getAt(int $position)
 * @method GpsrManufacturerDownloadTranslationEntity|null first()
 * @method GpsrManufacturerDownloadTranslationEntity|null last()
 */
class GpsrManufacturerDownloadTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrManufacturerDownloadTranslationEntity::class;
    }
}
