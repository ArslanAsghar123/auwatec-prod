<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Dal\Iterator\IteratorFactory\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class OrderByStruct extends DefaultStruct
{
    final public const ORDER__ASC = 'ASC';
    final public const ORDER__DESC = 'DESC';

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $order;

    public function __construct(string $field, string $order = self::ORDER__ASC)
    {
        $this->field = $field;
        $this->order = $order;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): OrderByStruct
    {
        $this->field = $field;
        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): OrderByStruct
    {
        $this->order = $order;
        return $this;
    }
}
