<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components\Struct;

use Acris\DiscountGroup\Custom\DiscountGroupCollection;
use Shopware\Core\Framework\Struct\Struct;

class LineItemDiscountStruct extends Struct
{
    private float $originalUnitPrice;

    protected DiscountGroupCollection $discountGroups;


    public function __construct( float $originalUnitPrice, DiscountGroupCollection $discountGroups )
    {
        $this->originalUnitPrice = $originalUnitPrice;
        $this->discountGroups =  $discountGroups;
    }

    /**
     * @return float
     */
    public function getOriginalUnitPrice(): float
    {
        return $this->originalUnitPrice;
    }

    /**
     * @param float $originalUnitPrice
     */
    public function setOriginalUnitPrice(float $originalUnitPrice): void
    {
        $this->originalUnitPrice = $originalUnitPrice;
    }

    /**
     * @return DiscountGroupCollection
     */
    public function getDiscountGroups(): DiscountGroupCollection
    {
        return $this->discountGroups;
    }

    /**
     * @param DiscountGroupCollection $discountGroups
     */
    public function setDiscountGroups($discountGroups): void
    {
        $this->discountGroups = $discountGroups;
    }


}