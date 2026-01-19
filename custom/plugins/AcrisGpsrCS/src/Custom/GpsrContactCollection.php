<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(GpsrContactEntity $entity)
 * @method void              set(string $key, GpsrContactEntity $entity)
 * @method GpsrContactEntity[]    getIterator()
 * @method GpsrContactEntity[]    getElements()
 * @method GpsrContactEntity|null get(string $key)
 * @method GpsrContactEntity|null getAt(int $position)
 * @method GpsrContactEntity|null first()
 * @method GpsrContactEntity|null last()
 */
class GpsrContactCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrContactEntity::class;
    }
}
