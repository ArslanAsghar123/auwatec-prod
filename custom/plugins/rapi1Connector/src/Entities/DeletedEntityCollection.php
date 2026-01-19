<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Entities;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(DeletedEntity $entity)
 * @method void              set(string $key, DeletedEntity $entity)
 * @method DeletedEntity[]    getIterator()
 * @method DeletedEntity[]    getElements()
 * @method DeletedEntity|null get(string $key)
 * @method DeletedEntity|null first()
 * @method DeletedEntity|null last()
 */
class DeletedEntityCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DeletedEntity::class;
    }
}