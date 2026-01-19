<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(ManufacturerDownloadEntity $entity)
 * @method void              set(string $key, ManufacturerDownloadEntity $entity)
 * @method ManufacturerDownloadEntity[]    getIterator()
 * @method ManufacturerDownloadEntity[]    getElements()
 * @method ManufacturerDownloadEntity|null get(string $key)
 * @method ManufacturerDownloadEntity|null getAt(int $position)
 * @method ManufacturerDownloadEntity|null first()
 * @method ManufacturerDownloadEntity|null last()
 */
class ManufacturerDownloadCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ManufacturerDownloadEntity::class;
    }
}
