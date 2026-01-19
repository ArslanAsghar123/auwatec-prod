<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Checkout\Promotion\Cart;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Checkout\Promotion\Cart\PromotionItemBuilder as ParentClass;

class PromotionItemBuilder extends ParentClass
{
    public const PROMOTION_PREVENT_DISCOUNT_GROUP_FIELD = 'acris_discount_group_promotion_prevent';

    public const PROMOTION_PREVENT_DISCOUNT_GROUP_KEY = 'acrisDiscountGroupPromotionPrevent';

    private ParentClass $parent;

    public function __construct(
        ParentClass $parent
    )
    {
        $this->parent = $parent;
    }

    public function buildPlaceholderItem(string $code): LineItem
    {
        return $this->parent->buildPlaceholderItem($code);
    }

    public function buildDiscountLineItem(string $code, PromotionEntity $promotion, PromotionDiscountEntity $discount, string $currencyId, float $currencyFactor = 1.0): LineItem
    {
        $promotionItem = $this->parent->buildDiscountLineItem($code, $promotion, $discount, $currencyId, $currencyFactor);
        return $this->checkPromotionPreventDiscountGroup($promotionItem, $promotion);
    }

    public function buildDeliveryPlaceholderLineItem(LineItem $discount, QuantityPriceDefinition $priceDefinition, CalculatedPrice $price): LineItem
    {
        return $this->parent->buildDeliveryPlaceholderLineItem($discount, $priceDefinition, $price);
    }

    private function checkPromotionPreventDiscountGroup(LineItem $promotionItem, PromotionEntity $promotion): LineItem
    {
        $preventDiscountGroup = !empty($promotion) && !empty($promotion->getCustomFields()) && is_array($promotion->getCustomFields()) && array_key_exists(self::PROMOTION_PREVENT_DISCOUNT_GROUP_FIELD, $promotion->getCustomFields()) ? $promotion->getCustomFields()[self::PROMOTION_PREVENT_DISCOUNT_GROUP_FIELD] : false;
        $promotionItem->setPayloadValue(self::PROMOTION_PREVENT_DISCOUNT_GROUP_KEY, $preventDiscountGroup);
        return $promotionItem;
    }
}
