<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Checkout\Decorated;

use Acris\DiscountGroup\Components\DiscountGroupService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartProcessor as ParentClass;

class CustomizedProductsCartProcessor extends ParentClass implements CartProcessorInterface
{
    public const CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE = 'customized-products';
    public const CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE = 'customized-products-option';
    public const DISCOUNT_GROUP_TYPE_PERCENTAGE = 'percentage';
    public const DISCOUNT_GROUP_CALCULATION_TYPE_DISCOUNT = 'discount';
    public const DISCOUNT_GROUP_CALCULATION_TYPE_SURCHARGE = 'surcharge';
    public const DISCOUNT_GROUP_ORIGINAL_CHILD_PRICE = 'originalChildPrice';

    public function __construct(
        private readonly ParentClass $parent,
        private readonly QuantityPriceCalculator $quantityPriceCalculator,
        private readonly PercentagePriceCalculator $percentagePriceCalculator,
        private readonly CashRounding $cashRounding
    ) {
    }

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        $this->parent->process($data, $original, $toCalculate, $context, $behavior);

        $this->checkCustomizedProducts($toCalculate, $context);
    }

    private function checkCustomizedProducts(Cart $cart, SalesChannelContext $context): void
    {
        if ($cart->getLineItems()->count() === 0) {
            return;
        }

        $customizedProductsLineItems = $cart->getLineItems()->filterType(self::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE);

        if ($customizedProductsLineItems->count() === 0) {
            return;
        }

        foreach ($customizedProductsLineItems as $customizedProductsLineItem) {
            if (!$customizedProductsLineItem->hasPayloadValue('acrisDiscountGroup') || empty($customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')) || !is_array($customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')) || !array_key_exists('discountGroups', $customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')) || empty($customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')['discountGroups']) || !is_array($customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')['discountGroups'])) {
                continue;
            }

            $discountGroups = $customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')['discountGroups'];

            foreach ($discountGroups as $discountGroup) {
                if (!array_key_exists('discount', $discountGroup) || empty($discountGroup['discount']) || !array_key_exists('discountType', $discountGroup) || empty($discountGroup['discountType']) || !array_key_exists('calculationType', $discountGroup) || empty($discountGroup['calculationType'])) {
                    continue;
                }

                $discount = abs($discountGroup['discount']);
                $discountType = $discountGroup['discountType'];
                $calculationType = $discountGroup['calculationType'];

                $this->calculateChildrenPrices($customizedProductsLineItem, $customizedProductsLineItem->getChildren()->filterType(self::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE), $discount, $discountType, $calculationType, $context);
            }

            $priceCollection = new PriceCollection($this->getPrices($customizedProductsLineItem));

            $price = $this->collectPrices($customizedProductsLineItem, $priceCollection, $context);

            $customizedProductsLineItem->setPrice($price);

            $this->displayDiscountInfo($customizedProductsLineItem, $context);
        }
    }

    private function calculateChildrenPrices(LineItem $lineItem, LineItemCollection $children, float $originalDiscount, string $discountType, string $calculationType, SalesChannelContext $context): void
    {
        if ($children->count() === 0) {
            return;
        }

        /** @var LineItem $child */
        foreach ($children->getElements() as $child) {
            if ($child->hasChildren()) {
                $this->calculateChildrenPrices($lineItem, $child->getChildren(), $originalDiscount, $discountType, $calculationType, $context);
            }

            if (empty($child->getPrice()) || !$child->getPrice() instanceof CalculatedPrice) {
                continue;
            }

            if (!empty($child->getPriceDefinition()) && $child->getPriceDefinition() instanceof PercentagePriceDefinition) {
                $this->setOriginalPercentagePrice($lineItem, $child, $context);
            }

            $price = $child->getPrice()->getUnitPrice();
            $quantity = $child->getQuantity();

            $discount = 0;

            if ($discountType === self::DISCOUNT_GROUP_TYPE_PERCENTAGE) {
                $discount = $price * ($originalDiscount / 100);
            }

            if ($calculationType === self::DISCOUNT_GROUP_CALCULATION_TYPE_DISCOUNT) {
                $price -= $discount;
            }

            if ($calculationType === self::DISCOUNT_GROUP_CALCULATION_TYPE_SURCHARGE) {
                $price += $discount;
            }

            $price = $this->cashRounding->cashRound($price, $context->getItemRounding());
            $originalPrice = $child->getPrice()->getUnitPrice();

            $newCalculatedPrice = new CalculatedPrice(
                $price,
                $price * $quantity,
                $child->getPrice()->getCalculatedTaxes(),
                $child->getPrice()->getTaxRules(),
                $quantity,
                $child->getPrice()->getReferencePrice(),
                $child->getPrice()->getListPrice(),
                $child->getPrice()->getRegulationPrice()
            );

            $newCalculatedPrice->addExtension(DiscountGroupService::DISCOUNT_GROUP_ORIGINAL_PRICE, $child->getPrice());

            $child->setPrice($newCalculatedPrice);

            $child->setPayloadValue(self::DISCOUNT_GROUP_ORIGINAL_CHILD_PRICE, $originalPrice);
        }
    }

    private function getPrices(LineItem $lineItem): array
    {
        $prices = [];

        foreach ($lineItem->getChildren() as $childLineItem) {
            if ($childLineItem->hasChildren()) {
                foreach ($this->getPrices($childLineItem) as $price) {
                    $prices[] = $price;
                }
            }

            if (!$childLineItem->getPrice() instanceof CalculatedPrice) {
                continue;
            }


            $prices[] = $childLineItem->getPrice();
        }

        return $prices;
    }

    private function filterOneTimeSurcharges(LineItem $customProductsLineItem): array
    {
        $prices = [];

        foreach ($customProductsLineItem->getChildren() as $childLineItem) {
            if ($childLineItem->hasChildren()) {
                foreach ($this->filterOneTimeSurcharges($childLineItem) as $price) {
                    $prices[] = $price;
                }
            }

            if ($childLineItem->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
                && $childLineItem->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
            ) {
                continue;
            }

            if (!$childLineItem->getPayloadValue('isOneTimeSurcharge')) {
                continue;
            }

            if (!$childLineItem->getPrice() instanceof CalculatedPrice) {
                continue;
            }

            $prices[] = $childLineItem->getPrice();
        }

        return $prices;
    }

    private function displayDiscountInfo(LineItem $customizedProductsLineItem, SalesChannelContext $context): void
    {
        if (empty($customizedProductsLineItem->getPrice()) || !$customizedProductsLineItem->getPrice() instanceof CalculatedPrice) {
            return;
        }

        $customProduct = $customizedProductsLineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();

        if (!$customizedProductsLineItem->hasPayloadValue('acrisListPrice') || empty($customizedProductsLineItem->getPayloadValue('acrisListPrice')) || !is_array($customizedProductsLineItem->getPayloadValue('acrisListPrice'))) {
            return;
        }

        if (empty($customProduct) || !$customProduct instanceof LineItem) {
            return;
        }

        if (!$customizedProductsLineItem->hasPayloadValue('acrisDiscountGroup') || empty($customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')) || !is_array($customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')) || !array_key_exists('originalUnitPrice', $customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')) || !is_float($customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')['originalUnitPrice'])) {
            return;
        }

        $originalUnitPrice = $customizedProductsLineItem->getPayloadValue('acrisDiscountGroup')['originalUnitPrice'];
        $quantity = $customProduct->getQuantity();

        $customProduct->setPrice(new CalculatedPrice(
            $originalUnitPrice,
            $originalUnitPrice * $quantity,
            $customProduct->getPrice()->getCalculatedTaxes(),
            $customProduct->getPrice()->getTaxRules(),
            $quantity,
            $customProduct->getPrice()->getReferencePrice(),
            $customProduct->getPrice()->getListPrice(),
            $customProduct->getPrice()->getRegulationPrice()
        ));

        $priceCollection = new PriceCollection($this->getPrices($customizedProductsLineItem));

        if ($priceCollection->count() === 0) {
            return;
        }

        $priceCollection = $this->collectOriginalPrices($priceCollection);
        $price = $this->collectPrices($customizedProductsLineItem, $priceCollection, $context);

        if ($price->getTotalPrice() < $customizedProductsLineItem->getPrice()->getTotalPrice()) {
            $customizedProductsLineItem->removePayloadValue('acrisListPrice');
            return;
        }

        $listPrice = $customizedProductsLineItem->getPayloadValue('acrisListPrice');
        $listPrice['unitPrice'] = $price->getUnitPrice();
        $listPrice['totalPrice'] = $price->getTotalPrice();

        $customizedProductsLineItem->setPayloadValue('acrisListPrice', $listPrice);
    }

    private function collectPrices(LineItem $customizedProductsLineItem, PriceCollection $priceCollection, SalesChannelContext $context): CalculatedPrice
    {
        $price = $priceCollection->sum();

        if ($customizedProductsLineItem->getPrice() !== null) {
            $oneTimeSurcharges = new PriceCollection($this->filterOneTimeSurcharges($customizedProductsLineItem));
            $oneTimeSurchargesPrice = $oneTimeSurcharges->sum()->getUnitPrice();

            $oneTimeSurchragesUnit = $oneTimeSurchargesPrice / $customizedProductsLineItem->getQuantity();

            $unitPrice = $price->getUnitPrice() - $oneTimeSurchargesPrice + $oneTimeSurchragesUnit;
            $price->assign([
                'unitPrice' => $this->cashRounding->cashRound($unitPrice, $context->getItemRounding()),
            ]);
        }

        return $price;
    }

    private function collectOriginalPrices(PriceCollection $priceCollection): PriceCollection
    {
        $newPriceCollection = new PriceCollection();

        foreach ($priceCollection->getElements() as $calculatedPrice) {
            if ($calculatedPrice->hasExtension(DiscountGroupService::DISCOUNT_GROUP_ORIGINAL_PRICE)) {
                $newPriceCollection->add($calculatedPrice->getExtension(DiscountGroupService::DISCOUNT_GROUP_ORIGINAL_PRICE));
                continue;
            }

            $newPriceCollection->add($calculatedPrice);
        }

        return $newPriceCollection;
    }

    private function setOriginalPercentagePrice(LineItem $lineItem, LineItem $child, SalesChannelContext $context): void
    {
        $customProduct = $lineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();

        if (empty($customProduct) || !$customProduct instanceof LineItem) {
            return;
        }

        if (!$lineItem->hasPayloadValue('acrisDiscountGroup') || empty($lineItem->getPayloadValue('acrisDiscountGroup')) || !is_array($lineItem->getPayloadValue('acrisDiscountGroup')) || !array_key_exists('originalUnitPrice', $lineItem->getPayloadValue('acrisDiscountGroup')) || !is_float($lineItem->getPayloadValue('acrisDiscountGroup')['originalUnitPrice'])) {
            return;
        }

        $customProduct = clone $customProduct;

        $originalUnitPrice = $lineItem->getPayloadValue('acrisDiscountGroup')['originalUnitPrice'];
        $quantity = $customProduct->getQuantity();

        $customProduct->setPrice(new CalculatedPrice(
            $originalUnitPrice,
            $originalUnitPrice * $quantity,
            $customProduct->getPrice()->getCalculatedTaxes(),
            $customProduct->getPrice()->getTaxRules(),
            $quantity,
            $customProduct->getPrice()->getReferencePrice(),
            $customProduct->getPrice()->getListPrice(),
            $customProduct->getPrice()->getRegulationPrice()
        ));

        $products = new LineItemCollection([$customProduct]);

        $price = $this->percentagePriceCalculator->calculate(
            $child->getPriceDefinition()->getPercentage(),
            $this->getPercentagePrices($products, $context),
            $context
        );

        if (!$child->getPayloadValue('isOneTimeSurcharge')) {
            $unitPriceDefinition = new QuantityPriceDefinition(
                $price->getUnitPrice(),
                $price->getTaxRules(),
                $child->getQuantity()
            );

            $price = $this->quantityPriceCalculator->calculate($unitPriceDefinition, $context);
        }

        $child->setPrice($price);
    }

    private function getPercentagePrices(LineItemCollection $products, SalesChannelContext $context): PriceCollection
    {
        $prices = $products->getPrices();

        $unitPrices = [];
        foreach ($prices as $price) {
            $unitPriceDefinition = new QuantityPriceDefinition(
                $price->getUnitPrice(),
                $price->getTaxRules()
            );

            $unitPrices[] = $this->quantityPriceCalculator->calculate($unitPriceDefinition, $context);
        }

        return new PriceCollection($unitPrices);
    }
}
