<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrContactDownloadTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(GpsrContactDownloadTranslationEntity $entity)
 * @method void                         set(string $key, GpsrContactDownloadTranslationEntity $entity)
 * @method GpsrContactDownloadTranslationEntity[]    getIterator()
 * @method GpsrContactDownloadTranslationEntity[]    getElements()
 * @method GpsrContactDownloadTranslationEntity|null get(string $key)
 * @method GpsrContactDownloadTranslationEntity|null getAt(int $position)
 * @method GpsrContactDownloadTranslationEntity|null first()
 * @method GpsrContactDownloadTranslationEntity|null last()
 */
class GpsrContactDownloadTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrContactDownloadTranslationEntity::class;
    }
}
