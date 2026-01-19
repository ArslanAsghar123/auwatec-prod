<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(GpsrNoteDownloadEntity $entity)
 * @method void              set(string $key, GpsrNoteDownloadEntity $entity)
 * @method GpsrNoteDownloadEntity[]    getIterator()
 * @method GpsrNoteDownloadEntity[]    getElements()
 * @method GpsrNoteDownloadEntity|null get(string $key)
 * @method GpsrNoteDownloadEntity|null getAt(int $position)
 * @method GpsrNoteDownloadEntity|null first()
 * @method GpsrNoteDownloadEntity|null last()
 */
class GpsrNoteDownloadCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrNoteDownloadEntity::class;
    }
}
