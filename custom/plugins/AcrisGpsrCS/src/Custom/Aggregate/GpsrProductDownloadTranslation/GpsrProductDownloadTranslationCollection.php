<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrProductDownloadTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(GpsrProductDownloadTranslationEntity $entity)
 * @method void                         set(string $key, GpsrProductDownloadTranslationEntity $entity)
 * @method GpsrProductDownloadTranslationEntity[]    getIterator()
 * @method GpsrProductDownloadTranslationEntity[]    getElements()
 * @method GpsrProductDownloadTranslationEntity|null get(string $key)
 * @method GpsrProductDownloadTranslationEntity|null getAt(int $position)
 * @method GpsrProductDownloadTranslationEntity|null first()
 * @method GpsrProductDownloadTranslationEntity|null last()
 */
class GpsrProductDownloadTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrProductDownloadTranslationEntity::class;
    }
}
