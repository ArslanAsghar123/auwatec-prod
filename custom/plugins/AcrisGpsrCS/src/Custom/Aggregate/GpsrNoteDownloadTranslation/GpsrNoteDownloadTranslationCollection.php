<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrNoteDownloadTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(GpsrNoteDownloadTranslationEntity $entity)
 * @method void                         set(string $key, GpsrNoteDownloadTranslationEntity $entity)
 * @method GpsrNoteDownloadTranslationEntity[]    getIterator()
 * @method GpsrNoteDownloadTranslationEntity[]    getElements()
 * @method GpsrNoteDownloadTranslationEntity|null get(string $key)
 * @method GpsrNoteDownloadTranslationEntity|null getAt(int $position)
 * @method GpsrNoteDownloadTranslationEntity|null first()
 * @method GpsrNoteDownloadTranslationEntity|null last()
 */
class GpsrNoteDownloadTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrNoteDownloadTranslationEntity::class;
    }
}
