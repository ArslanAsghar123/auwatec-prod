<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrManufacturerTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(GpsrManufacturerTranslationEntity $entity)
 * @method void                         set(string $key, GpsrManufacturerTranslationEntity $entity)
 * @method GpsrManufacturerTranslationEntity[]    getIterator()
 * @method GpsrManufacturerTranslationEntity[]    getElements()
 * @method GpsrManufacturerTranslationEntity|null get(string $key)
 * @method GpsrManufacturerTranslationEntity|null getAt(int $position)
 * @method GpsrManufacturerTranslationEntity|null first()
 * @method GpsrManufacturerTranslationEntity|null last()
 */
class GpsrManufacturerTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrManufacturerTranslationEntity::class;
    }
}
