<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Content\Product\Cart;

use Acris\DiscountGroup\Components\DiscountGroupService;
use Acris\DiscountGroup\Components\ScaleDiscountService;
use Acris\DiscountGroup\Components\Struct\LineItemDiscountStruct;
use Acris\DiscountGroup\Custom\DiscountGroupEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ListPrice;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class DiscountInfoCartProcessor implements CartDataCollectorInterface
{
    public const CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE = 'customized-products';
    public const DISCOUNT_GROUP_ORIGINAL_CHILD_PRICE = 'originalChildPrice';
    private ScaleDiscountService $scaleDiscountService;

    private SystemConfigService $systemConfigService;

    public function __construct( ScaleDiscountService $scaleDiscountService, SystemConfigService $systemConfigService )
    {
        $this->scaleDiscountService = $scaleDiscountService;
        $this->systemConfigService = $systemConfigService;
    }

    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        $this->scaleDiscountService->checkScaleDiscounts($original->getLineItems(), $data, $context);

        $lineItems = $original
            ->getLineItems();

        $discountDisplayUnitPrice = $this->systemConfigService->get('AcrisDiscountGroupCS.config.discountAtUnitPrice', $context->getSalesChannelId() );
        $discountDisplayTotalPrice = $this->systemConfigService->get('AcrisDiscountGroupCS.config.discountAtSubtotal', $context->getSalesChannelId() );

        $originalQuantity = [];
        $productsWithoutDiscount =
            $context->getExtension(DiscountGroupService::ACRIS_PRODUCTS_IDS_WITHOUT_DISCOUNT_KEY) ??
            new ArrayEntity([]);

        foreach ($lineItems as $lineItem) {

            if(!$this->checkIfIsValidProduct($lineItem)) {
                continue;
            }

            $originalQuantity[$lineItem->getId()] = $lineItem->getQuantity();

            if($productsWithoutDiscount->has($lineItem->getReferencedId())) {
                if($lineItem->hasPayloadValue('acrisListPrice')) {
                    $lineItem->removePayloadValue('acrisListPrice');
                }
            }

            if ($lineItem->hasPayloadValue('discountGroupScalePriceQuantity') && is_int($lineItem->getPayloadValue('discountGroupScalePriceQuantity'))) {
                $lineItem->assign(['quantity' => $lineItem->getPayloadValue('discountGroupScalePriceQuantity')]);
            }

            $lineItemType = $lineItem->getType();

            /** @var SalesChannelProductEntity $product */
            $product = $data->get('product-' . $lineItem->getReferencedId());

            if($lineItemType === 'customized-products') {
                $productLineItem = $lineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();
                $productReferenceId = $productLineItem->getReferencedId();
                $product = $data->get('product-' . $productReferenceId);
            }

            if($product instanceof SalesChannelProductEntity) {
                $this->addListPricePayload($lineItem, $product, $discountDisplayUnitPrice, $discountDisplayTotalPrice, $originalQuantity[$lineItem->getId()]);
                $this->addDiscountGroupPayload($lineItem, $product);
            }
        }

        if (empty($originalQuantity)) {
            return;
        }

        foreach ($lineItems as $lineItem) {

            if(!$this->checkIfIsValidProduct($lineItem)) {
                continue;
            }

            if (array_key_exists($lineItem->getId(), $originalQuantity) && is_int($originalQuantity[$lineItem->getId()])) {
                $lineItem->assign(['quantity' => $originalQuantity[$lineItem->getId()]]);
            }
        }
    }

    private function checkIfIsValidProduct(LineItem $lineItem) : bool
    {
        if($lineItem->getType() !== 'product' && $lineItem->getType() !== 'customized-products') {
            return false;
        }
        return true;
    }

    private function addListPricePayload(LineItem $lineItem, SalesChannelProductEntity $product, string $discountDisplayUnitPrice, string $discountDisplayTotalPrice, int $originalQuantity): void
    {
        $price = $product->getCalculatedPrice();
        if ($product->getCalculatedPrices()->count() === 0) {
            $this->addListPriceFromCalculatedPrice($lineItem, $price, $discountDisplayUnitPrice, $discountDisplayTotalPrice, $originalQuantity,$product);
            return;
        }

        // keep loop reference to $price variable to get last quantity price in case of "null"
        foreach ($product->getCalculatedPrices() as $price) {
            if ($lineItem->getQuantity() <= $price->getQuantity()) {
                $this->addListPriceFromCalculatedPrice($lineItem, $price, $discountDisplayUnitPrice, $discountDisplayTotalPrice, $originalQuantity,$product);
                return;
            }
        }

        // if no price was found, use the last one
        foreach ($product->getCalculatedPrices() as $price) {
            $this->addListPriceFromCalculatedPrice($lineItem, $price, $discountDisplayUnitPrice, $discountDisplayTotalPrice, $originalQuantity,$product);
            return;
        }
    }

    private function addListPriceFromCalculatedPrice(LineItem $lineItem, CalculatedPrice $calculatedPrice, string $discountDisplayUnitPrice, string $discountDisplayTotalPrice, int $originalQuantity, SalesChannelProductEntity $product): void
    {
        $payloadValue = [];
        $listPrice = $calculatedPrice->getListPrice();
        $isProductCustomizedProduct = $product->getExtension('swagCustomizedProductsTemplate');

        if($listPrice instanceof ListPrice && !$isProductCustomizedProduct) {
            $payloadValue['unitPrice'] = $listPrice->getPrice();
            $payloadValue['unitPercentage'] = $listPrice->getPercentage();
            $payloadValue['totalPrice'] = $originalQuantity * $listPrice->getPrice();
            $payloadValue['totalPercentage'] = $listPrice->getPercentage();
        }

        if(!$isProductCustomizedProduct) {
            $lineItem->setPayloadValue('acrisListPrice', $payloadValue);
            return;
        }

        $calulatedPriceUnitPrice = $calculatedPrice->getUnitPrice();
        $listPricePrice = $listPrice?->getPrice();
        $listPricePrice = $listPricePrice ?? 0;

        $lineItemUnitPrice = $lineItem->getPrice()?->getUnitPrice();
        $optionPrice = $lineItemUnitPrice - $calulatedPriceUnitPrice ;
        $originalUnitPrice = $listPricePrice + $optionPrice;

        if($listPrice instanceof ListPrice) {
            $payloadValue['unitPrice'] = $originalUnitPrice;
            $payloadValue['unitPercentage'] = $listPrice->getPercentage();
            $payloadValue['totalPrice'] = $originalQuantity * $originalUnitPrice;
            $payloadValue['totalPercentage'] = $listPrice->getPercentage();
        }

        $lineItem->setPayloadValue('acrisListPrice', $payloadValue);
    }

    private function addDiscountGroupPayload( LineItem  $lineItem, SalesChannelProductEntity $product )
    {
        $payloadValue = [];
        if($product->hasExtension(DiscountGroupService::ACRIS_DISCOUNT_GROUP_LINE_ITEM_DISCOUNT) === true) {
            /** @var LineItemDiscountStruct $lineItemDiscountStruct */
            $lineItemDiscountStruct = $product->getExtension(DiscountGroupService::ACRIS_DISCOUNT_GROUP_LINE_ITEM_DISCOUNT);

            $payloadValue['originalUnitPrice'] = $lineItemDiscountStruct->getOriginalUnitPrice();
            $payloadDiscountGroups = [];

            /** @var DiscountGroupEntity $discountGroup */
            foreach( $lineItemDiscountStruct->getDiscountGroups() as $discountGroup )
            {
                $payloadDiscountGroups[] = array(
                    'id' => $discountGroup->getId(),
                    'internalName' => $discountGroup->getInternalName(),
                    'internalId' => $discountGroup->getInternalId(),
                    'active' => $discountGroup->getActive(),
                    'activeFrom' => $discountGroup->getActiveFrom(),
                    'activeUntil' => $discountGroup->getActiveUntil(),
                    'priority' => $discountGroup->getPriority(),
                    'discount' => $discountGroup->getDiscount(),
                    'discountType' => $discountGroup->getDiscountType(),
                    'calculationType' => $discountGroup->getCalculationType(),
                    'displayName' => $discountGroup->getTranslation('displayName')
                );
            }

            $payloadValue['discountGroups'] = $payloadDiscountGroups;
        }

        $lineItem->setPayloadValue('acrisDiscountGroup', $payloadValue );
    }
}
