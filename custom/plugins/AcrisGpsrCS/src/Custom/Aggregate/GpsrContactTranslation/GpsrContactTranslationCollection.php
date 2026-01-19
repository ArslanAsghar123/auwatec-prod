<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrContactTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(GpsrContactTranslationEntity $entity)
 * @method void                         set(string $key, GpsrContactTranslationEntity $entity)
 * @method GpsrContactTranslationEntity[]    getIterator()
 * @method GpsrContactTranslationEntity[]    getElements()
 * @method GpsrContactTranslationEntity|null get(string $key)
 * @method GpsrContactTranslationEntity|null getAt(int $position)
 * @method GpsrContactTranslationEntity|null first()
 * @method GpsrContactTranslationEntity|null last()
 */
class GpsrContactTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrContactTranslationEntity::class;
    }
}
