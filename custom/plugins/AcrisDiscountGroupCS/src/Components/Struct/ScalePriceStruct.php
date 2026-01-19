<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components\Struct;

use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Framework\Struct\Struct;

class ScalePriceStruct extends Struct
{
    private int $from;
    private ?int $to;
    private CalculatedPrice $calculatedPrice;

    public function __construct(int $from, ?int $to, CalculatedPrice $calculatedPrice)
    {
        $this->from = $from;
        $this->to = $to;
        $this->calculatedPrice = $calculatedPrice;
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @param int $from
     */
    public function setFrom(int $from): void
    {
        $this->from = $from;
    }

    /**
     * @return int|null
     */
    public function getTo(): ?int
    {
        return $this->to;
    }

    /**
     * @param int|null $to
     */
    public function setTo(?int $to): void
    {
        $this->to = $to;
    }

    /**
     * @return CalculatedPrice
     */
    public function getCalculatedPrice(): CalculatedPrice
    {
        return $this->calculatedPrice;
    }

    /**
     * @param CalculatedPrice $calculatedPrice
     */
    public function setCalculatedPrice(CalculatedPrice $calculatedPrice): void
    {
        $this->calculatedPrice = $calculatedPrice;
    }
}
