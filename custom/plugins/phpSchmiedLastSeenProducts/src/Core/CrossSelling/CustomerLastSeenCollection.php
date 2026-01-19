<?php declare(strict_types=1);
namespace phpSchmied\LastSeenProducts\Core\CrossSelling;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(CustomerLastSeenEntity $entity)
 * @method void set(string $key, CustomerLastSeenEntity $entity)
 * @method CustomerLastSeenEntity[] getIterator()
 * @method CustomerLastSeenEntity[] getElements()
 * @method CustomerLastSeenEntity|null get(string $key)
 * @method CustomerLastSeenEntity|null first()
 * @method CustomerLastSeenEntity|null last()
 * */
class CustomerLastSeenCollection extends EntityCollection
{

    protected function getExpectedClass(): string
    {
        return CustomerLastSeenEntity::class;
    }
}
