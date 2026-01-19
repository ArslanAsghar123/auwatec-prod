<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Framework\DataAbstractionLayer\Pricing;

class CashRoundingConfig extends \Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig
{
    private int $originalDecimals;

    public function __construct(        int $decimals,
                                        float $interval,
                                        bool $roundForNet,
                                        int $originalDecimals = 0)
    {
        $this->originalDecimals = $originalDecimals;
        parent::__construct($decimals,$interval,$roundForNet);
    }

    public function setOriginalDecimals(int $originalDecimals) : void
    {
        $this->originalDecimals = $originalDecimals;
    }

    public function getOriginalDecimals() : int
    {
        return $this->originalDecimals;
    }
}