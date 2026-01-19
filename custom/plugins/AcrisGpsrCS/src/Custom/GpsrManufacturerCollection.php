<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(GpsrManufacturerEntity $entity)
 * @method void              set(string $key, GpsrManufacturerEntity $entity)
 * @method GpsrManufacturerEntity[]    getIterator()
 * @method GpsrManufacturerEntity[]    getElements()
 * @method GpsrManufacturerEntity|null get(string $key)
 * @method GpsrManufacturerEntity|null getAt(int $position)
 * @method GpsrManufacturerEntity|null first()
 * @method GpsrManufacturerEntity|null last()
 */
class GpsrManufacturerCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrManufacturerEntity::class;
    }
}
