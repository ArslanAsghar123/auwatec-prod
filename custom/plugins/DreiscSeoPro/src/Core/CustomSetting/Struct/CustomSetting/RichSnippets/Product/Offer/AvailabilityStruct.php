<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Offer;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class AvailabilityStruct extends AbstractCustomSettingStruct
{
    final const AVAILABILITY__IN_STOCK = 'InStock';
    final const AVAILABILITY__LIMITED_AVAILABILITY = 'LimitedAvailability';
    final const AVAILABILITY__IN_STORE_ONLY = 'InStoreOnly';
    final const AVAILABILITY__ONLINE_ONLY = 'OnlineOnly';
    final const AVAILABILITY__DISCONTINUED = 'Discontinued';
    final const AVAILABILITY__OUT_OF_STOCK = 'OutOfStock';
    final const AVAILABILITY__PRE_ORDER = 'PreOrder';
    final const AVAILABILITY__PRE_SALE = 'PreSale';
    final const AVAILABILITY__SOLD_OUT = 'SoldOut';

    /**
     * @var string|null
     */
    protected $defaultAvailability;

    /**
     * @var string|null
     */
    protected $defaultAvailabilityOutOfStock;

    /**
     * @var string|null
     */
    protected $defaultAvailabilityClearanceSale;

    /**
     * @var string|null
     */
    protected $defaultAvailabilityClearanceSaleOutOfStock;

    /**
     * @param string|null $defaultAvailability
     * @param string|null $defaultAvailabilityOutOfStock
     * @param string|null $defaultAvailabilityClearanceSale
     * @param string|null $defaultAvailabilityClearanceSaleOutOfStock
     * @param string $settingContext
     */
    public function __construct(?string $defaultAvailability, ?string $defaultAvailabilityOutOfStock, ?string $defaultAvailabilityClearanceSale, ?string $defaultAvailabilityClearanceSaleOutOfStock, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->defaultAvailability = $defaultAvailability;
        $this->defaultAvailabilityOutOfStock = $defaultAvailabilityOutOfStock;
        $this->defaultAvailabilityClearanceSale = $defaultAvailabilityClearanceSale;
        $this->defaultAvailabilityClearanceSaleOutOfStock = $defaultAvailabilityClearanceSaleOutOfStock;
    }

    public function getDefaultAvailability(): ?string
    {
        return $this->defaultAvailability;
    }

    public function setDefaultAvailability(?string $defaultAvailability): AvailabilityStruct
    {
        $this->defaultAvailability = $defaultAvailability;

        return $this;
    }

    public function getDefaultAvailabilityOutOfStock(): ?string
    {
        return $this->defaultAvailabilityOutOfStock;
    }

    public function setDefaultAvailabilityOutOfStock(?string $defaultAvailabilityOutOfStock): AvailabilityStruct
    {
        $this->defaultAvailabilityOutOfStock = $defaultAvailabilityOutOfStock;

        return $this;
    }

    public function getDefaultAvailabilityClearanceSale(): ?string
    {
        return $this->defaultAvailabilityClearanceSale;
    }

    public function setDefaultAvailabilityClearanceSale(?string $defaultAvailabilityClearanceSale): AvailabilityStruct
    {
        $this->defaultAvailabilityClearanceSale = $defaultAvailabilityClearanceSale;

        return $this;
    }

    public function getDefaultAvailabilityClearanceSaleOutOfStock(): ?string
    {
        return $this->defaultAvailabilityClearanceSaleOutOfStock;
    }

    public function setDefaultAvailabilityClearanceSaleOutOfStock(?string $defaultAvailabilityClearanceSaleOutOfStock): AvailabilityStruct
    {
        $this->defaultAvailabilityClearanceSaleOutOfStock = $defaultAvailabilityClearanceSaleOutOfStock;

        return $this;
    }
}
