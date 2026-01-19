<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components;

use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ScaleDiscountService
{
    private SystemConfigService $systemConfigService;

    private AbstractProductPriceCalculator $priceCalculator;

    public function __construct(
        SystemConfigService $systemConfigService,
        AbstractProductPriceCalculator $priceCalculator
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->priceCalculator = $priceCalculator;
    }

    public function checkScaleDiscounts(LineItemCollection $lineItems, CartDataCollection $data, SalesChannelContext $salesChannelContext): void
    {
        if ($this->systemConfigService->getBool('AcrisDiscountGroupCS.config.activateAcrossScaleDiscounts', $salesChannelContext->getSalesChannel()->getId()) !== true) {
            return;
        }

        $ids = $this->collectDiscountGroupIds($lineItems);

        if (empty($ids)) {
            return;
        }

        $this->checkScaleDiscountsByQuantity($lineItems, $data, $ids, $salesChannelContext);
    }

    private function collectDiscountGroupIds(LineItemCollection $lineItems): array
    {
        $ids = [];
        /** @var LineItem $lineItem */
        foreach ($lineItems as $lineItem) {
            if (!$lineItem->hasPayloadValue('acrisDiscountGroup') || empty($lineItem->getPayloadValue('acrisDiscountGroup'))
                || !is_array($lineItem->getPayloadValue('acrisDiscountGroup')) || !array_key_exists('discountGroups', $lineItem->getPayloadValue('acrisDiscountGroup'))
                || empty($lineItem->getPayloadValue('acrisDiscountGroup')['discountGroups']) || !is_array($lineItem->getPayloadValue('acrisDiscountGroup')['discountGroups'])) {
                continue;
            }

            $discountGroups = $this->sortDiscountGroupsByPriority($lineItem->getPayloadValue('acrisDiscountGroup')['discountGroups']);

            foreach ($discountGroups as $discountGroup) {
                if (empty($discountGroup) || !is_array($discountGroup) || !array_key_exists('id', $discountGroup) || empty($discountGroup['id'])) {
                    continue;
                }

                $ids[$discountGroup['id']][$lineItem->getReferencedId()] = $lineItem->getQuantity();
            }
        }

        return $ids;
    }

    private function sortDiscountGroupsByPriority(array $discountGroups): array
    {
        usort($discountGroups, static function (array $discountGroupA, array $discountGroupB) {
            $priorityA = 0;
            $priorityB = 0;

            if (!empty($discountGroupA) && array_key_exists('priority', $discountGroupA)) {
                $priorityA = $discountGroupA['priority'];
            }

            if (!empty($discountGroupB) && array_key_exists('priority', $discountGroupB)) {
                $priorityB = $discountGroupB['priority'];
            }
            return $priorityA < $priorityB;
        });

        return $discountGroups;
    }

    private function checkScaleDiscountsByQuantity(LineItemCollection $lineItems, CartDataCollection $data, array $ids, SalesChannelContext $context): void
    {
        /** @var LineItem $lineItem */
        foreach ($lineItems as $lineItem) {
            if (!$lineItem->hasPayloadValue('acrisDiscountGroup') || empty($lineItem->getPayloadValue('acrisDiscountGroup'))
                || !is_array($lineItem->getPayloadValue('acrisDiscountGroup')) || !array_key_exists('discountGroups', $lineItem->getPayloadValue('acrisDiscountGroup'))
                || empty($lineItem->getPayloadValue('acrisDiscountGroup')['discountGroups']) || !is_array($lineItem->getPayloadValue('acrisDiscountGroup')['discountGroups'])) {
                continue;
            }

            $id = $lineItem->getReferencedId();

            $product = $data->get(
                $this->getDataKey((string) $id)
            );

            // no data for enrich exists
            if (!$product instanceof SalesChannelProductEntity) {
                return;
            }

            $discountGroups = $this->sortDiscountGroupsByPriority($lineItem->getPayloadValue('acrisDiscountGroup')['discountGroups']);

            $quantity = 0;
            $quantitySet = false;
            $discountGroupId = null;
            $discountGroupDisplayName = null;

            foreach ($discountGroups as $discountGroup) {
                if (empty($discountGroup) || !is_array($discountGroup) || !array_key_exists('id', $discountGroup) || empty($discountGroup['id'])) {
                    continue;
                }

                $key = $discountGroup['id'];

                if (array_key_exists($key, $ids) && !empty($ids[$key]) && is_array($ids[$key]) && \count($ids[$key]) > 0 && array_key_exists($lineItem->getReferencedId(), $ids[$key]) && !empty($ids[$key][$lineItem->getReferencedId()])) {
                    foreach ($ids[$key] as $scalePriceQuantity) {
                        $quantity += $scalePriceQuantity;
                        $quantitySet = true;
                    }
                }

                if ($quantitySet === true) {
                    $discountGroupId = $key;
                    if (array_key_exists('displayName', $discountGroup) && !empty($discountGroup['displayName'])) {
                        $discountGroupDisplayName = $discountGroup['displayName'];
                    }
                    break;
                }
            }

            if ($quantitySet === true && !empty($discountGroupId)) {
                $lineItem->setPriceDefinition(
                    $this->getPriceDefinition($product, $context, $quantity)
                );
                $lineItem->setPayloadValue('discountGroupScalePriceId', $discountGroupId);
                $lineItem->setPayloadValue('discountGroupScalePriceDisplayName', $discountGroupDisplayName);
                $lineItem->setPayloadValue('discountGroupScalePriceQuantity', $quantity);
            }
        }
    }

    private function getDataKey(string $id): string
    {
        return 'product-' . $id;
    }

    private function getPriceDefinition(SalesChannelProductEntity $product, SalesChannelContext $context, int $quantity): QuantityPriceDefinition
    {
        $this->priceCalculator->calculate([$product], $context);

        if ($product->getCalculatedPrices()->count() === 0) {
            return $this->buildPriceDefinition($product->getCalculatedPrice(), $quantity);
        }

        // keep loop reference to $price variable to get last quantity price in case of "null"
        $price = $product->getCalculatedPrice();
        foreach ($product->getCalculatedPrices() as $price) {
            if ($quantity <= $price->getQuantity()) {
                break;
            }
        }

        return $this->buildPriceDefinition($price, $quantity);
    }

    private function buildPriceDefinition(CalculatedPrice $price, int $quantity): QuantityPriceDefinition
    {
        $definition = new QuantityPriceDefinition($price->getUnitPrice(), $price->getTaxRules(), $quantity);
        if ($price->getListPrice() !== null) {
            $definition->setListPrice($price->getListPrice()->getPrice());
        }

        if ($price->getReferencePrice() !== null) {
            $definition->setReferencePriceDefinition(
                new ReferencePriceDefinition(
                    $price->getReferencePrice()->getPurchaseUnit(),
                    $price->getReferencePrice()->getReferenceUnit(),
                    $price->getReferencePrice()->getUnitName()
                )
            );
        }

        return $definition;
    }
}
