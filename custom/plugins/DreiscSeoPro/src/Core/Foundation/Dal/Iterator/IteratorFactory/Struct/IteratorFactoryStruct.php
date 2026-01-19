<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Dal\Iterator\IteratorFactory\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;

class IteratorFactoryStruct extends DefaultStruct
{
    final const ORDER_BY__DISABLED = 'disabled';
    final const ORDER_BY__AUTO_INCREMENT = 'auto_increment';

    /**
     * @param OrderByStruct[] $orderByStructs
     */
    public function __construct(private string $entityDefinitionClass, private int $offset = 0, private int $limit = 50, private array $orderByStructs = [], private string $idField = 'id')
    {
    }

    public function getEntityDefinitionClass(): string
    {
        return $this->entityDefinitionClass;
    }

    public function setEntityDefinitionClass(string $entityDefinitionClass): IteratorFactoryStruct
    {
        $this->entityDefinitionClass = $entityDefinitionClass;
        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): IteratorFactoryStruct
    {
        $this->offset = $offset;
        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): IteratorFactoryStruct
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return OrderByStruct[]
     */
    public function getOrderByStructs(): array
    {
        return $this->orderByStructs;
    }

    /**
     * @param OrderByStruct[] $orderByStructs
     */
    public function setOrderByStructs(array $orderByStructs): IteratorFactoryStruct
    {
        $this->orderByStructs = $orderByStructs;
        return $this;
    }

    public function getIdField(): string
    {
        return $this->idField;
    }

    public function setIdField(string $idField): IteratorFactoryStruct
    {
        $this->idField = $idField;
        return $this;
    }
}
