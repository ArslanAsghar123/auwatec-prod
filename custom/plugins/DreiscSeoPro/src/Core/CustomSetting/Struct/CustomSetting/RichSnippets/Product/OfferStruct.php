<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Offer\AvailabilityStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Offer\ItemConditionStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Offer\SellerStruct;

class OfferStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__SELLER__NAME = '';

    final const DEFAULT__AVAILABILITY__DEFAULT_AVAILABILITY = AvailabilityStruct::AVAILABILITY__IN_STOCK;
    final const DEFAULT__AVAILABILITY__DEFAULT_AVAILABILITY_OUT_OF_STOCK = AvailabilityStruct::AVAILABILITY__OUT_OF_STOCK;
    final const DEFAULT__AVAILABILITY__DEFAULT_AVAILABILITY_CLEARANCE_SALE = AvailabilityStruct::AVAILABILITY__LIMITED_AVAILABILITY;
    final const DEFAULT__AVAILABILITY__DEFAULT_AVAILABILITY_CLEARANCE_SALE_OUT_OF_STOCK = AvailabilityStruct::AVAILABILITY__SOLD_OUT;

    final const DEFAULT__ITEM_CONDITION__DEFAULT_ITEM_CONDITION = ItemConditionStruct::ITEM_CONDITION__NEW_CONDITION;
    /**
     * @var SellerStruct
     */
    protected $seller;

    /**
     * @var AvailabilityStruct
     */
    protected $availability;

    /**
     * @var ItemConditionStruct
     */
    protected $itemCondition;

    /**
     * @param array $offerSettings
     * @param string $settingContext
     */
    public function __construct(array $offerSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->seller = new SellerStruct(
            !empty($offerSettings['seller']['name']) ? $offerSettings['seller']['name'] : $this->setDefault(self::DEFAULT__SELLER__NAME),
            $settingContext
        );

        $this->availability = new AvailabilityStruct(
            !empty($offerSettings['availability']['defaultAvailability']) ? $offerSettings['availability']['defaultAvailability'] : $this->setDefault(self::DEFAULT__AVAILABILITY__DEFAULT_AVAILABILITY),
            !empty($offerSettings['availability']['defaultAvailabilityOutOfStock']) ? $offerSettings['availability']['defaultAvailabilityOutOfStock'] : $this->setDefault(self::DEFAULT__AVAILABILITY__DEFAULT_AVAILABILITY_OUT_OF_STOCK),
            !empty($offerSettings['availability']['defaultAvailabilityClearanceSale']) ? $offerSettings['availability']['defaultAvailabilityClearanceSale'] : $this->setDefault(self::DEFAULT__AVAILABILITY__DEFAULT_AVAILABILITY_CLEARANCE_SALE),
            !empty($offerSettings['availability']['defaultAvailabilityClearanceSaleOutOfStock']) ? $offerSettings['availability']['defaultAvailabilityClearanceSaleOutOfStock'] : $this->setDefault(self::DEFAULT__AVAILABILITY__DEFAULT_AVAILABILITY_CLEARANCE_SALE_OUT_OF_STOCK),
            $settingContext
        );

        $this->itemCondition = new ItemConditionStruct(
            !empty($offerSettings['itemCondition']['defaultItemCondition']) ? $offerSettings['itemCondition']['defaultItemCondition'] : $this->setDefault(self::DEFAULT__ITEM_CONDITION__DEFAULT_ITEM_CONDITION),
            $settingContext
        );
    }

    public function getSeller(): SellerStruct
    {
        return $this->seller;
    }

    public function setSeller(SellerStruct $seller): OfferStruct
    {
        $this->seller = $seller;

        return $this;
    }

    public function getAvailability(): AvailabilityStruct
    {
        return $this->availability;
    }

    public function setAvailability(AvailabilityStruct $availability): OfferStruct
    {
        $this->availability = $availability;

        return $this;
    }

    public function getItemCondition(): ItemConditionStruct
    {
        return $this->itemCondition;
    }

    public function setItemCondition(ItemConditionStruct $itemCondition): OfferStruct
    {
        $this->itemCondition = $itemCondition;
        return $this;
    }
}
