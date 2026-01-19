<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(DiscountGroupEntity $entity)
 * @method void              set(string $key, DiscountGroupEntity $entity)
 * @method DiscountGroupEntity[]    getIterator()
 * @method DiscountGroupEntity[]    getElements()
 * @method DiscountGroupEntity|null get(string $key)
 * @method DiscountGroupEntity|null first()
 * @method DiscountGroupEntity|null last()
 */
class DiscountGroupCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DiscountGroupEntity::class;
    }
}
