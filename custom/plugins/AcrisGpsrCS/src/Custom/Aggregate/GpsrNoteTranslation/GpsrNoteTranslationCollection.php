<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrNoteTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(GpsrNoteTranslationEntity $entity)
 * @method void                         set(string $key, GpsrNoteTranslationEntity $entity)
 * @method GpsrNoteTranslationEntity[]    getIterator()
 * @method GpsrNoteTranslationEntity[]    getElements()
 * @method GpsrNoteTranslationEntity|null get(string $key)
 * @method GpsrNoteTranslationEntity|null getAt(int $position)
 * @method GpsrNoteTranslationEntity|null first()
 * @method GpsrNoteTranslationEntity|null last()
 */
class GpsrNoteTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrNoteTranslationEntity::class;
    }
}
