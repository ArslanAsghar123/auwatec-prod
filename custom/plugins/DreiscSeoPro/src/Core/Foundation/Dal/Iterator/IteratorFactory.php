<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Dal\Iterator;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Foundation\Dal\Iterator\IteratorFactory\Struct\IteratorFactoryStruct;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\OffsetQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;

class IteratorFactory
{
    public function __construct(private readonly Connection $connection, private readonly DefinitionInstanceRegistry $definitionInstanceRegistry)
    {
    }

    public function createIdIterator(IteratorFactoryStruct $iteratorFactoryStruct): OffsetQuery
    {
        $definition = $this->definitionInstanceRegistry->get(
            $iteratorFactoryStruct->getEntityDefinitionClass()
        );
        $entity = $definition->getEntityName();

        /** Create the base query */
        $escaped = EntityDefinitionQueryHelper::escape($entity);
        $query = $this->connection->createQueryBuilder();
        $query->from($escaped);

        /** Set the offset and limit */
        $query->setFirstResult($iteratorFactoryStruct->getOffset());
        $query->setMaxResults($iteratorFactoryStruct->getLimit());

        /** Order by */
        foreach($iteratorFactoryStruct->getOrderByStructs() as $orderByStruct) {
            $query->addOrderBy($orderByStruct->getField(), $orderByStruct->getOrder());
        }

        /** Select only the id field */
        $query->select([$escaped . '.id', 'LOWER(HEX(' . $escaped . '.' . $iteratorFactoryStruct->getIdField() .  '))']);

        return new OffsetQuery($query);
    }
}
