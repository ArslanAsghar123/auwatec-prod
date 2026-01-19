<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
* @method void                add(DreiscSeoBulkEntity $entity)
* @method void                set(string $key, DreiscSeoBulkEntity $entity)
* @method DreiscSeoBulkEntity[]    getIterator()
* @method DreiscSeoBulkEntity[]    getElements()
* @method DreiscSeoBulkEntity|null get(string $key)
* @method DreiscSeoBulkEntity|null first()
* @method DreiscSeoBulkEntity|null last()
*/
class DreiscSeoBulkCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DreiscSeoBulkEntity::class;
    }
}

