<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                         add(DiscountGroupTranslationEntity $entity)
 * @method void                         set(string $key, DiscountGroupTranslationEntity $entity)
 * @method DiscountGroupTranslationEntity[]    getIterator()
 * @method DiscountGroupTranslationEntity[]    getElements()
 * @method DiscountGroupTranslationEntity|null get(string $key)
 * @method DiscountGroupTranslationEntity|null first()
 * @method DiscountGroupTranslationEntity|null last()
 */
class DiscountGroupTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DiscountGroupTranslationEntity::class;
    }
}
