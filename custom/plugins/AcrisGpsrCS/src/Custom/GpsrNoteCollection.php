<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(GpsrNoteEntity $entity)
 * @method void              set(string $key, GpsrNoteEntity $entity)
 * @method GpsrNoteEntity[]    getIterator()
 * @method GpsrNoteEntity[]    getElements()
 * @method GpsrNoteEntity|null get(string $key)
 * @method GpsrNoteEntity|null getAt(int $position)
 * @method GpsrNoteEntity|null first()
 * @method GpsrNoteEntity|null last()
 */
class GpsrNoteCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrNoteEntity::class;
    }
}
