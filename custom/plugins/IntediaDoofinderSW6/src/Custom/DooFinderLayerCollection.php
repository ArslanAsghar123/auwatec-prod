<?php declare(strict_types=1);

namespace Intedia\Doofinder\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(DooFinderLayerEntity $entity)
 * @method void              set(string $key, DooFinderLayerEntity $entity)
 * @method DooFinderLayerEntity[]    getIterator()
 * @method DooFinderLayerEntity[]    getElements()
 * @method DooFinderLayerEntity|null get(string $key)
 * @method DooFinderLayerEntity|null first()
 * @method DooFinderLayerEntity|null last()
 */
class DooFinderLayerCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DooFinderLayerEntity::class;
    }
}
