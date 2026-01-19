<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components;

use Acris\DiscountGroup\Components\Struct\LineItemDiscountStruct;
use Acris\DiscountGroup\Components\Struct\ScalePriceCollection;
use Acris\DiscountGroup\Components\Struct\ScalePriceStruct;
use Acris\DiscountGroup\Custom\DiscountGroupCollection;
use Acris\DiscountGroup\Custom\DiscountGroupDefinition;
use Acris\DiscountGroup\Custom\DiscountGroupEntity;
use Shopware\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ListPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\RegulationPrice;
use Shopware\Core\Checkout\Cart\Tax\AbstractTaxDetector;
use Shopware\Core\Checkout\Promotion\Exception\DiscountCalculatorNotFoundException;
use Shopware\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CalculatedCheapestPrice;
use Shopware\Core\Content\Product\SalesChannel\Price\ReferencePriceDto;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\Unit\UnitCollection;

class DiscountGroupService
{
    public const PERCENTAGE_DISCOUNT_ROUNDING_DECIMALS = 5;
    public const PERCENTAGE_DISCOUNT_ROUNDING_INTERVAL = 0.00001;
    public const ACRIS_STREAM_IDS_EXTENSION = 'acrisStreamIds';
    public const ACRIS_SALES_CHANNEL_PRODUCT_NO_BOX_EXTENSION = 'acrisSalesChannelProductNoBox';
    public const DISCOUNT_GROUP_ORIGINAL_PRICE = 'discountGroupOriginalPrice';
    const ACRIS_DISCOUNT_GROUP_LINE_ITEM_DISCOUNT = 'acrisDiscountGroupLineItemDiscount';
    const ACRIS_RRP_PRICE_EXTENSION_KEY = 'acris_rrp_price_price_struct';
    public const ACRIS_PRODUCTS_IDS_WITHOUT_DISCOUNT_KEY = 'acrisProductsWithoutDiscount';
    private $originalCustomizedProductCalculatedPrice = [];


    private ?UnitCollection $units = null;

    public function __construct(
        private readonly DiscountGroupGateway $discountGroupGateway,
        private readonly AbsolutePriceCalculator $absolutePriceCalculator,
        private readonly PercentagePriceCalculator $percentagePriceCalculator,
        private readonly EntityRepository $unitRepository,
        private readonly QuantityPriceCalculator $calculator,
        private readonly CashRounding $priceRounding,
        private readonly AbstractTaxDetector $taxDetector,
        private readonly SalesChannelRepository $salesChannelProductRepository,
    ) { }

    public function calculateProductPrices(iterable $products, SalesChannelContext $salesChannelContext): void
    {
        $productsWithoutDiscount = $salesChannelContext->getExtension('productsWithDiscount') ??
            new ArrayEntity([]);
        /** @var Entity $product */
        foreach ($products as $product) {

            $isProductCustomizedProduct = $product->getExtension('swagCustomizedProductsTemplate');

            if($isProductCustomizedProduct && !isset($this->originalCustomizedProductCalculatedPrice[$product->getId()])) {
                $this->originalCustomizedProductCalculatedPrice[$product->getId()] = $product->get('calculatedPrice');
            }

            if ($product->hasExtension(self::ACRIS_STREAM_IDS_EXTENSION) && !empty($product->getExtension(self::ACRIS_STREAM_IDS_EXTENSION))) {
                $productStreamIds = $product->getExtension(self::ACRIS_STREAM_IDS_EXTENSION)->get('ids');
            } else {
                $productStreamIds = $this->discountGroupGateway->getProductStreamIds([$product->get("id")], $salesChannelContext->getContext());
                $product->addExtension(self::ACRIS_STREAM_IDS_EXTENSION, new ArrayEntity(['ids' => $productStreamIds]));
            }

            $discountGroupResult = $this->discountGroupGateway->getAllDiscountGroupsForProduct($salesChannelContext, $product->get("id"), $productStreamIds);

            if ($discountGroupResult->count() === 0) {
                if(!$productsWithoutDiscount->has($product->get('id'))) {
                    $productsWithoutDiscount->set($product->get("id"),$product->get("id"));
                    $salesChannelContext->addExtension(self::ACRIS_PRODUCTS_IDS_WITHOUT_DISCOUNT_KEY,$productsWithoutDiscount);
                }
                continue;
            }

            $skipCalculatingCheapestProductPrice = false;
            if($product->get("calculatedCheapestPrice") instanceof CalculatedCheapestPrice && $product->get("calculatedCheapestPrice")->getVariantId() !== $product->get("id")) {
                $skipCalculatingCheapestProductPrice = $this->calculateCheapestPricesOfDifferentVariant($product, $salesChannelContext);
            }

            $this->enrichDiscountGroups($product, $discountGroupResult);
            $this->calculateProductPricesByDiscountGroups($product, $discountGroupResult, $salesChannelContext, $skipCalculatingCheapestProductPrice);
        }
    }

    public function reset(): void
    {
        $this->units = null;
    }

    private function enrichDiscountGroups(Entity $product, EntitySearchResult $discountGroupResult): void
    {
        /** @var DiscountGroupEntity $discountGroup */
        foreach ($discountGroupResult->getElements() as $discountGroup) {
            $discountGroup->setProductIds(array_unique(array_merge(!empty($discountGroup->getProductIds()) ? $discountGroup->getProductIds() : [], [$product->get('id')])));
        }
    }

    private function calculateProductPricesByDiscountGroups(Entity $product, EntitySearchResult $discountGroupResult, SalesChannelContext $salesChannelContext, bool $skipCalculatingCheapestProductPrice): void
    {
        // product assignment
        $discountGroupCollection = $this->filterDiscountGroup($discountGroupResult, (string) $product->get("id"), $product);

        if($discountGroupCollection->count() === 0) {
            return;
        }

        $this->sortDiscounts($discountGroupCollection);

        $this->addLineItemDiscountData( $product, $discountGroupCollection );

        $this->calculatePrices($product, $discountGroupCollection, $salesChannelContext, $skipCalculatingCheapestProductPrice);
    }

    private function calculatePrices(Entity $product, DiscountGroupCollection $discountGroupCollection, SalesChannelContext $salesChannelContext, bool $skipCalculatingCheapestProductPrice): void
    {
        $this->buildCalculatedPrices($product, $discountGroupCollection, $salesChannelContext);

        foreach ($discountGroupCollection->getElements() as $discountGroupEntity) {
            if($discountGroupEntity->getDiscount() <= 0) continue;
            if(empty($discountGroupEntity->getMinQuantity())) $discountGroupEntity->setMinQuantity(1);
            $calculatedPrice = $this->originalCustomizedProductCalculatedPrice[$product->getId()] ?? $product->get("calculatedPrice");

            $product->assign(["calculatedPrices" => $this->calculatePriceCollection($product->get("calculatedPrices"), $discountGroupEntity, $product, $salesChannelContext)]);
            $product->assign(["calculatedPrice" => ($this->calculatePrice($calculatedPrice, $discountGroupEntity, $product, $salesChannelContext, false))]);

            if($skipCalculatingCheapestProductPrice !== true && $product->get("calculatedCheapestPrice") instanceof CalculatedCheapestPrice) {
                $cheapestPriceNew = CalculatedCheapestPrice::createFrom($this->calculatePrice($product->get("calculatedCheapestPrice"), $discountGroupEntity, $product, $salesChannelContext, false));
                $cheapestPriceNew->setHasRange($product->get("calculatedCheapestPrice")->hasRange());
                $product->assign(["calculatedCheapestPrice" => $cheapestPriceNew]);
            }
        }
    }

    private function calculatePriceCollection(?PriceCollection $calculatedPrices, DiscountGroupEntity $discountGroupEntity, Entity $product, SalesChannelContext $salesChannelContext): ?PriceCollection
    {
        if(!$calculatedPrices instanceof PriceCollection) return $calculatedPrices;
        if($calculatedPrices->count() === 0) return $calculatedPrices;
        $calculatedPricesNew = new PriceCollection();
        $lastQuantity = $calculatedPrices->last()->getQuantity();
        foreach ($calculatedPrices as $calculatedPrice) {
            if(!$calculatedPrice instanceof CalculatedPrice) {
                return $calculatedPrices;
            }
            $lastQuantity === $calculatedPrice->getQuantity() ? $isLast = true : $isLast = false;
            $calculatedPricesNew->add($this->calculatePrice($calculatedPrice, $discountGroupEntity, $product, $salesChannelContext, $isLast));
        }
        $calculatedPricesNew->setExtensions($calculatedPrices->getExtensions());
        return $calculatedPricesNew;
    }

    private function calculatePrice(?CalculatedPrice $calculatedPrice, DiscountGroupEntity $discountGroupEntity, Entity $product, SalesChannelContext $salesChannelContext, $isLast = true): ?CalculatedPrice
    {
        if(!$calculatedPrice instanceof CalculatedPrice) return $calculatedPrice;
        // before we built the prices, so this check is the only check which is needed
        if(($isLast === true && ($discountGroupEntity->getMinQuantity() >= $calculatedPrice->getQuantity() || $discountGroupEntity->getMaxQuantity() === null))
            || ($isLast === false && ($discountGroupEntity->getMinQuantity() <= $calculatedPrice->getQuantity() && ($discountGroupEntity->getMaxQuantity() === null || $discountGroupEntity->getMaxQuantity() >= $calculatedPrice->getQuantity())))) {
            // continue here
        } else {
            return $calculatedPrice;
        }

        $discount = $this->getPositive($discountGroupEntity->getDiscount());
        if($discountGroupEntity->getDiscountType() === DiscountGroupDefinition::DISCOUNT_TYPE_ABSOLUTE) {
            // prevent product prices smaller then 0
            if($discount > $calculatedPrice->getUnitPrice()) {
                $discount = $calculatedPrice->getUnitPrice();
            }
            $this->setCalculationBase($calculatedPrice, $discountGroupEntity, $product, $salesChannelContext);
            $calculatedDiscount = $this->absolutePriceCalculator->calculate($this->getSurchargeOrDiscount($discountGroupEntity, $discount), new PriceCollection([$calculatedPrice]), $salesChannelContext);
        } elseif($discountGroupEntity->getDiscountType() === DiscountGroupDefinition::DISCOUNT_TYPE_PERCENTAGE) {
            // prevent percentage bigger then 100
            if($discount > 100) {
                $discount = 100;
            }
            $this->setCalculationBase($calculatedPrice, $discountGroupEntity, $product, $salesChannelContext);

            $newCalculatedPrice = new CalculatedPrice(
                $calculatedPrice->getUnitPrice(),
                $calculatedPrice->getUnitPrice(),
                $calculatedPrice->getCalculatedTaxes(),
                $calculatedPrice->getTaxRules(),
                1,
                $calculatedPrice->getReferencePrice(),
                $calculatedPrice->getListPrice(),
                $calculatedPrice->getRegulationPrice()
            );
            $extensions = $calculatedPrice->getExtensions();
            $newCalculatedPrice->setExtensions($extensions);
            // to calculate the percentage discount value we need to set the rounding
            $orgRounding = $salesChannelContext->getItemRounding();
            $salesChannelContext->setItemRounding(new CashRoundingConfig(self::PERCENTAGE_DISCOUNT_ROUNDING_DECIMALS, self::PERCENTAGE_DISCOUNT_ROUNDING_INTERVAL, $orgRounding->roundForNet()));
            $calculatedDiscount = $this->percentagePriceCalculator->calculate($this->getSurchargeOrDiscount($discountGroupEntity, $discount), new PriceCollection([$newCalculatedPrice]), $salesChannelContext);
            $salesChannelContext->setItemRounding($orgRounding);
        } else {
            throw new DiscountCalculatorNotFoundException($discountGroupEntity->getDiscountType());
        }
        $calculatedPriceNew = (new PriceCollection([$calculatedPrice, $calculatedDiscount]))->sum();
        $listPriceNew = null;

        if ($calculatedPriceNew->getUnitPrice() < 0) {
            $calculatedPriceZero = new CalculatedPrice(
                0,
                $calculatedPriceNew->getUnitPrice(),
                $calculatedPriceNew->getCalculatedTaxes(),
                $calculatedPriceNew->getTaxRules(),
                $calculatedPriceNew->getQuantity(),
                $calculatedPriceNew->getReferencePrice(),
                $calculatedPriceNew->getListPrice(),
                $calculatedPriceNew->getRegulationPrice()
            );
            $calculatedPriceNew = $calculatedPriceZero;
        } else {
            $calculatedPriceNew = $this->calculatePriceFromDefinition($product, $calculatedPriceNew, $calculatedPriceNew->getQuantity(), $salesChannelContext);
        }

        if ($calculatedPriceNew->getUnitPrice() > 0) {
            switch ($discountGroupEntity->getListPriceType()) {
                case DiscountGroupDefinition::LIST_PRICE_TYPE_IGNORE:
                    if($calculatedPrice->getListPrice() && $calculatedPrice->getListPrice()->getPrice()) {
                        $listPriceNew = ListPrice::createFromUnitPrice($calculatedPriceNew->getUnitPrice(), $calculatedPrice->getListPrice()->getPrice());
                    } else {
                        $listPriceNew = null;
                    }
                    break;
                case DiscountGroupDefinition::LIST_PRICE_TYPE_SET:
                    if($calculatedPrice->getListPrice() && $calculatedPrice->getListPrice()->getPrice()) {
                        $listPriceNew = ListPrice::createFromUnitPrice($calculatedPriceNew->getUnitPrice(), $calculatedPrice->getListPrice()->getPrice());
                    } else {
                        $listPriceNew = ListPrice::createFromUnitPrice($calculatedPriceNew->getUnitPrice(), $calculatedPrice->getUnitPrice());
                    }
                    break;
                case DiscountGroupDefinition::LIST_PRICE_TYPE_SET_PRICE:
                    $listPriceNew = ListPrice::createFromUnitPrice($calculatedPriceNew->getUnitPrice(), $calculatedPrice->getUnitPrice());
                    break;
                case DiscountGroupDefinition::LIST_PRICE_TYPE_RRP:
                    $tax = $this->taxDetector->getTaxState($salesChannelContext);
                    $rrp = 0;
                    if($discountGroupEntity->getRrpTaxDisplay() === DiscountGroupDefinition::RRP_TAX_GROSS
                        || ($discountGroupEntity->getRrpTaxDisplay() === DiscountGroupDefinition::RRP_TAX_AUTO && $tax === CartPrice::TAX_STATE_GROSS)) {
                        $rrp = $this->getRrp($calculatedPrice, CartPrice::TAX_STATE_GROSS);
                    } elseif ($discountGroupEntity->getRrpTaxDisplay() === DiscountGroupDefinition::RRP_TAX_NET
                        || ($discountGroupEntity->getRrpTaxDisplay() === DiscountGroupDefinition::RRP_TAX_AUTO && $tax !== CartPrice::TAX_STATE_GROSS)) {
                        $rrp = $this->getRrp($calculatedPrice, CartPrice::TAX_STATE_NET);
                    }

                    if($rrp > 0) {
                        $listPriceNew = ListPrice::createFromUnitPrice($calculatedPriceNew->getUnitPrice(), $rrp);
                    } else {
                        $listPriceNew = null;
                    }
                    break;
                case DiscountGroupDefinition::LIST_PRICE_TYPE_SET_RRP:
                    $tax = $this->taxDetector->getTaxState($salesChannelContext);
                    $rrp = 0;
                    if($discountGroupEntity->getRrpTaxDisplay() === DiscountGroupDefinition::RRP_TAX_GROSS
                        || ($discountGroupEntity->getRrpTaxDisplay() === DiscountGroupDefinition::RRP_TAX_AUTO && $tax === CartPrice::TAX_STATE_GROSS)) {
                        $rrp = $this->getRrp($calculatedPrice, CartPrice::TAX_STATE_GROSS);
                    } elseif ($discountGroupEntity->getRrpTaxDisplay() === DiscountGroupDefinition::RRP_TAX_NET
                        || ($discountGroupEntity->getRrpTaxDisplay() === DiscountGroupDefinition::RRP_TAX_AUTO && $tax !== CartPrice::TAX_STATE_GROSS)) {
                        $rrp = $this->getRrp($calculatedPrice, CartPrice::TAX_STATE_NET);
                    }

                    if($rrp > 0) {
                        $listPriceNew = ListPrice::createFromUnitPrice($calculatedPriceNew->getUnitPrice(), $rrp);
                    } else {
                        $listPriceNew = ListPrice::createFromUnitPrice($calculatedPriceNew->getUnitPrice(), $calculatedPrice->getUnitPrice());
                    }
                    break;
                case DiscountGroupDefinition::LIST_PRICE_TYPE_SET_PURCHASE_PRICE:
                    $purchasePrice = $this->getPurchasePriceFromProduct($product, $salesChannelContext);

                    if($purchasePrice > 0) {
                        $listPriceNew = ListPrice::createFromUnitPrice($calculatedPriceNew->getUnitPrice(), $purchasePrice);
                    } else {
                        $listPriceNew = null;
                    }
                    break;
                case DiscountGroupDefinition::LIST_PRICE_TYPE_REMOVE:
                    $listPriceNew = null;
                    break;
                default:
                    $listPriceNew = null;
            }
        }

        $newCalculatedPrice = new CalculatedPrice(
            $calculatedPriceNew->getUnitPrice(),
            $calculatedPriceNew->getTotalPrice(),
            $calculatedPriceNew->getCalculatedTaxes(),
            $calculatedPriceNew->getTaxRules(),
            $calculatedPrice->getQuantity(),
            $this->calculateReferencePriceByReferencePrice($calculatedPriceNew->getUnitPrice(), $calculatedPrice->getReferencePrice(), $salesChannelContext->getItemRounding()),
            $listPriceNew,
            $calculatedPrice->getRegulationPrice()
        );

        $newCalculatedPrice->addExtension(self::DISCOUNT_GROUP_ORIGINAL_PRICE, $calculatedPrice);

        return $newCalculatedPrice;
    }

    private function getSurchargeOrDiscount(DiscountGroupEntity $discountGroupEntity, float $discount): float
    {
        if($discountGroupEntity->getCalculationType() === DiscountGroupDefinition::CALCULATION_TYPE_SURCHARGE) {
            return $this->getPositive($discount);
        } else {
            return $this->getNegative($discount);
        }
    }

    private function getNegative(float $value): float
    {
        return -1 * abs($value);
    }

    private function getPositive(float $value): float
    {
        return abs($value);
    }

    private function sortDiscounts(DiscountGroupCollection $discountGroupCollection): void
    {
        $priority = null;
        $exclude = false;
        foreach ($discountGroupCollection->getElements() as $discountGroupEntity) {
            if ($exclude && !empty($priority) && $discountGroupEntity->getPriority() < $priority) {
                $discountGroupCollection->remove($discountGroupEntity->getId());
            }
            if ($discountGroupEntity->getExcluded()) {
                $exclude = true;
            }

            $priority = $discountGroupEntity->getPriority();
        }
    }

    private function addLineItemDiscountData(Entity $product, DiscountGroupCollection $discountGroupCollection): void
    {
        if(!$product->get("calculatedPrice") instanceof CalculatedPrice) return;
        $lineItemDiscount = new LineItemDiscountStruct($product->get("calculatedPrice")->getUnitPrice(), $discountGroupCollection);

        $product->addExtension( self::ACRIS_DISCOUNT_GROUP_LINE_ITEM_DISCOUNT, $lineItemDiscount );
    }

    private function buildCalculatedPrices(Entity $product, DiscountGroupCollection $discountGroupCollection, SalesChannelContext $salesChannelContext): void
    {
        if($this->isScalePriceRebuildNeeded($discountGroupCollection) === false) {
            return;
        }

        // if product has no calculated prices, we have to set it
        if(empty($product->get("calculatedPrices")) || $product->get("calculatedPrices")->count() === 0) {
            $product->assign(["calculatedPrices" => new PriceCollection([$product->get("calculatedPrice")])]);
        }

        // build price struct
        $scalePriceCollection = new ScalePriceCollection();
        $quantityLast = $product->get("calculatedPrices")->last()->getQuantity();
        $quantityFromBefore = 1;
        foreach ($product->get("calculatedPrices") as $calculatedPrice) {
            if($calculatedPrice->getQuantity() === $quantityLast) {
                $scalePriceCollection->add(new ScalePriceStruct($calculatedPrice->getQuantity(), null, $calculatedPrice));
            } else {
                $scalePriceCollection->add(new ScalePriceStruct($quantityFromBefore, $calculatedPrice->getQuantity(), $calculatedPrice));
                $quantityFromBefore = $calculatedPrice->getQuantity() + 1;
            }
        }

        // now we add our discounts
        // first we add minimum
        foreach ($discountGroupCollection->getElements() as $discountGroup) {
            $minValue = $discountGroup->getMinQuantity();
            if(empty($minValue) === true) {
                $minValue = 1;
            }
            /** @var ScalePriceStruct $scalePrice */
            foreach ($scalePriceCollection->getElements() as $scalePrice) {
                if($minValue === $scalePrice->getFrom()) {
                    continue 2;
                }
            }

            /** @var ScalePriceStruct $scalePrice */
            foreach ($scalePriceCollection->getElements() as $scalePriceKey => $scalePrice) {
                if($minValue > $scalePrice->getFrom()
                    && ($scalePrice->getTo() === null || $minValue <= $scalePrice->getTo())) {
                    $scalePriceCollection->add(new ScalePriceStruct($scalePrice->getFrom(), $minValue - 1, $scalePrice->getCalculatedPrice()));
                    $scalePriceCollection->add(new ScalePriceStruct($minValue, $scalePrice->getTo(), $scalePrice->getCalculatedPrice()));
                    $scalePriceCollection->remove($scalePriceKey);
                }
            }
        }

        // now we add our discounts
        // afterward we add maximum
        foreach ($discountGroupCollection->getElements() as $discountGroup) {
            $maxValue = $discountGroup->getMaxQuantity();
            /** @var ScalePriceStruct $scalePrice */
            foreach ($scalePriceCollection->getElements() as $scalePrice) {
                if($maxValue === $scalePrice->getTo()) {
                    continue 2;
                }
            }

            /** @var ScalePriceStruct $scalePrice */
            foreach ($scalePriceCollection->getElements() as $scalePriceKey => $scalePrice) {
                if($maxValue >= $scalePrice->getFrom()
                    && ($maxValue < $scalePrice->getTo() || $scalePrice->getTo() === null)) {
                    $scalePriceCollection->add(new ScalePriceStruct($scalePrice->getFrom(), $maxValue, $scalePrice->getCalculatedPrice()));
                    $scalePriceCollection->add(new ScalePriceStruct($maxValue + 1, $scalePrice->getTo(), $scalePrice->getCalculatedPrice()));
                    $scalePriceCollection->remove($scalePriceKey);
                }
            }
        }

        // sort scale prices quantity asc
        $scalePriceCollection->sort(function (ScalePriceStruct $a, ScalePriceStruct $b) {
            return $a->getFrom() <=> $b->getFrom();
        });

        // at the end we build back the calculated prices
        $priceCollection = new PriceCollection();
        $quantityLast = $scalePriceCollection->last()->getFrom();

        foreach ($scalePriceCollection as $scalePrice) {
            $calculatedPrice = $scalePrice->getCalculatedPrice();
            if($quantityLast === $scalePrice->getFrom()) {
                $quantity = $scalePrice->getFrom();
            } else {
                $quantity = $scalePrice->getTo();
            }
            if($quantity === $calculatedPrice->getQuantity()) {
                $priceCollection->add($calculatedPrice);
            } else {
                $priceCollection->add($this->calculatePriceFromDefinition($product, $calculatedPrice, $quantity, $salesChannelContext));
            }
        }

        if($product->get("calculatedPrices") instanceof PriceCollection && empty($product->get("calculatedPrices")->getExtensions()) === false) $priceCollection->setExtensions($product->get("calculatedPrices")->getExtensions());

        $product->assign(["calculatedPrices" => $priceCollection]);
    }

    private function isScalePriceRebuildNeeded(DiscountGroupCollection $discountGroupCollection): bool
    {
        foreach ($discountGroupCollection->getElements() as $discountGroup) {
            if($discountGroup->getMinQuantity() > 1 || $discountGroup->getMaxQuantity() !== null) {
                return true;
            }
        }
        return false;
    }

    /*
     * Copied and adapted from ProductPriceCalculator
     * */
    private function buildDefinition(
        Entity $product,
        float $price,
        SalesChannelContext $context,
        UnitCollection $units,
        ReferencePriceDto $reference,
        int $quantity = 1,
        ?float $listPrice = null,
        ?float $regulationPrice = null
    ): QuantityPriceDefinition {
        $taxId = $product->get('taxId');
        $definition = new QuantityPriceDefinition($price, $context->buildTaxRules($taxId), $quantity);
        $definition->setReferencePriceDefinition(
            $this->buildReferencePriceDefinition($reference, $units)
        );
        $definition->setListPrice($listPrice);
        $definition->setRegulationPrice($regulationPrice);

        return $definition;
    }

    private function buildReferencePriceDefinition(ReferencePriceDto $definition, UnitCollection $units): ?ReferencePriceDefinition
    {
        if ($definition->getPurchase() === null || $definition->getPurchase() <= 0) {
            return null;
        }
        if ($definition->getUnitId() === null) {
            return null;
        }
        if ($definition->getReference() === null || $definition->getReference() <= 0) {
            return null;
        }
        if ($definition->getPurchase() === $definition->getReference()) {
            return null;
        }

        $unit = $units->get($definition->getUnitId());
        if ($unit === null) {
            return null;
        }

        return new ReferencePriceDefinition(
            $definition->getPurchase(),
            $definition->getReference(),
            $unit->getTranslation('name')
        );
    }

    private function getUnits(SalesChannelContext $context): UnitCollection
    {
        if ($this->units !== null) {
            return $this->units;
        }

        /** @var UnitCollection $units */
        $units = $this->unitRepository
            ->search(new Criteria(), $context->getContext())
            ->getEntities();

        return $this->units = $units;
    }

    /**
     * Copied and adapted from GrossPriceCalculator and NetPriceCalculator
     */
    private function calculateReferencePriceByReferencePrice(float $price, ?ReferencePrice $referencePrice, CashRoundingConfig $config): ?ReferencePrice
    {
        if (!$referencePrice instanceof ReferencePrice) {
            return $referencePrice;
        }

        if ($referencePrice->getPurchaseUnit() <= 0 || $referencePrice->getReferenceUnit() <= 0) {
            return null;
        }

        $price = $price / $referencePrice->getPurchaseUnit() * $referencePrice->getReferenceUnit();

        $price = $this->priceRounding->mathRound($price, $config);

        return new ReferencePrice(
            $price,
            $referencePrice->getPurchaseUnit(),
            $referencePrice->getReferenceUnit(),
            $referencePrice->getUnitName()
        );
    }

    private function setCalculationBase(CalculatedPrice $calculatedPrice, DiscountGroupEntity $discountGroupEntity, Entity $product, SalesChannelContext $salesChannelContext): void
    {
        $newUnitPrice = null;
        if($discountGroupEntity->getCalculationBase() === DiscountGroupDefinition::CALCULATION_BASE_LIST_PRICE) {
            if ($calculatedPrice->getListPrice() instanceof ListPrice && $calculatedPrice->getListPrice()->getPrice() > 0) {
                $newUnitPrice = $calculatedPrice->getListPrice()->getPrice();
            }
        } elseif($discountGroupEntity->getCalculationBase() === DiscountGroupDefinition::CALCULATION_BASE_PURCHASE_PRICE) {
            $purchasePrice = $this->getPurchasePriceFromProduct($product, $salesChannelContext);
            if($purchasePrice > 0) {
                $newUnitPrice = $purchasePrice;
            }
        } elseif($discountGroupEntity->getCalculationBase() === DiscountGroupDefinition::CALCULATION_BASE_RRP) {
            $tax = $this->taxDetector->getTaxState($salesChannelContext);
            $rrp = 0;
            if($discountGroupEntity->getRrpTax() === DiscountGroupDefinition::RRP_TAX_GROSS
                || ($discountGroupEntity->getRrpTax() === DiscountGroupDefinition::RRP_TAX_AUTO && $tax === CartPrice::TAX_STATE_GROSS)) {
                $rrp = $this->getRrp($calculatedPrice, CartPrice::TAX_STATE_GROSS);
            } elseif ($discountGroupEntity->getRrpTax() === DiscountGroupDefinition::RRP_TAX_NET
                || ($discountGroupEntity->getRrpTax() === DiscountGroupDefinition::RRP_TAX_AUTO && $tax !== CartPrice::TAX_STATE_GROSS)) {
                $rrp = $this->getRrp($calculatedPrice, CartPrice::TAX_STATE_NET);
            }

            if($rrp > 0) {
                $newUnitPrice = $rrp;
            }
        }

        if($newUnitPrice !== null) {
            $calculatedPrice->assign(['unitPrice' => $newUnitPrice]);
            $calculatedPrice->assign(['totalPrice' => $newUnitPrice * $calculatedPrice->getQuantity()]);
        }
    }

    private function getRrp(CalculatedPrice $calculatedPrice, string $tax = CartPrice::TAX_STATE_GROSS): float
    {
        $rrp = 0;
        if($calculatedPrice->hasExtension(self::ACRIS_RRP_PRICE_EXTENSION_KEY) && $calculatedPrice->getExtension(self::ACRIS_RRP_PRICE_EXTENSION_KEY)->getRrpPricePrice()) {
            $rrpPrice = $calculatedPrice->getExtension(self::ACRIS_RRP_PRICE_EXTENSION_KEY)->getRrpPricePrice();
            if($tax === CartPrice::TAX_STATE_GROSS && $rrpPrice->getGross() > 0) {
                $rrp = $rrpPrice->getGross();
            } elseif($tax !== CartPrice::TAX_STATE_GROSS && $rrpPrice->getNet() > 0) {
                $rrp = $rrpPrice->getNet();
            }
        }
        return (float) $rrp;
    }

    private function getPurchasePriceFromProduct(Entity $product, SalesChannelContext $salesChannelContext): float
    {
        if($product->get("purchasePrices") instanceof \Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection) {
            /** @var Price $purchasePrice */
            foreach ($product->get("purchasePrices")->getElements() as $purchasePrice) {
                if($purchasePrice->getCurrencyId() === $salesChannelContext->getCurrencyId()) {
                    return $this->getPurchasePriceValue($purchasePrice, $salesChannelContext);
                }
            }
        }
        return 0;
    }

    private function getPurchasePriceValue(Price $purchasePrice, SalesChannelContext $salesChannelContext): float
    {
        $tax = $this->taxDetector->getTaxState($salesChannelContext);
        if($tax === CartPrice::TAX_STATE_GROSS) {
            $purchasePriceValue = $purchasePrice->getGross();
        } else {
            $purchasePriceValue = $purchasePrice->getNet();
        }
        return $purchasePriceValue;
    }

    /**
     * @param EntitySearchResult $discountGroupResult
     * @param string             $productId
     * @param Entity             $product
     * @return DiscountGroupCollection
     */
    private function filterDiscountGroup(EntitySearchResult $discountGroupResult, string $productId, Entity $product): DiscountGroupCollection
    {
        return $discountGroupResult->getEntities()->filter(function (DiscountGroupEntity $discountGroupEntity) use ($productId, $product) {
            return ($discountGroupEntity->getProductAssignmentType() === DiscountGroupDefinition::PRODUCT_ASSIGNMENT_TYPE_PRODUCT && !empty($discountGroupEntity->getProductId()) && $productId === $discountGroupEntity->getProductId())
                || (!empty($discountGroupEntity->getProductIds()) && $discountGroupEntity->getProductAssignmentType() !== DiscountGroupDefinition::PRODUCT_ASSIGNMENT_TYPE_MATERIAL_GROUP && in_array($productId, $discountGroupEntity->getProductIds()))
                || ($discountGroupEntity->getProductAssignmentType() === DiscountGroupDefinition::PRODUCT_ASSIGNMENT_TYPE_MATERIAL_GROUP
                    && !empty($discountGroupEntity->getMaterialGroup()) && !empty($product->getTranslated()) && array_key_exists('customFields', $product->getTranslated())
                    && !empty($product->getTranslated()['customFields']) && (array_key_exists('acris_discount_group_value', $product->getTranslated()['customFields'])
                        && !empty($product->getTranslated()['customFields']['acris_discount_group_value'])
                        && $product->getTranslated()['customFields']['acris_discount_group_value'] === $discountGroupEntity->getMaterialGroup())
                    || (is_array($product->getTranslated()['customFields']) && array_key_exists('acris_discount_group_product_value', $product->getTranslated()['customFields']))
                    && !empty($product->getTranslated()['customFields']['acris_discount_group_product_value'])
                    && $product->getTranslated()['customFields']['acris_discount_group_product_value'] === $discountGroupEntity->getMaterialGroup());
        });
    }

    private function calculateCheapestPricesOfDifferentVariant(Entity $product, SalesChannelContext $salesChannelContext): bool
    {
        $cheapestPriceVariantId = $product->get("calculatedCheapestPrice")->getVariantId();

        if ($cheapestPriceVariantId === null) return false;

        try {
            $criteria = new Criteria([$cheapestPriceVariantId]);

            $salesChannelContext->addExtension(self::ACRIS_SALES_CHANNEL_PRODUCT_NO_BOX_EXTENSION, new ArrayEntity());

            $cheapestPriceVariant = $this->salesChannelProductRepository->search($criteria, $salesChannelContext)->first();

            $salesChannelContext->removeExtension(self::ACRIS_SALES_CHANNEL_PRODUCT_NO_BOX_EXTENSION);
        } catch(\Throwable) {
            $salesChannelContext->removeExtension(self::ACRIS_SALES_CHANNEL_PRODUCT_NO_BOX_EXTENSION);
            return false;
        }

        if ($cheapestPriceVariant === null) return false;

        $product->assign(["calculatedCheapestPrice" => $cheapestPriceVariant->get("calculatedCheapestPrice")]);

        return true;
    }

    public function calculatePriceFromDefinition(Entity $product, CalculatedPrice $calculatedPrice, int $quantity, SalesChannelContext $salesChannelContext): CalculatedPrice
    {
        $units = $this->getUnits($salesChannelContext);
        $reference = ReferencePriceDto::createFromEntity($product);
        $calculatedPrice->getListPrice() instanceof ListPrice ? $listPrice = $calculatedPrice->getListPrice()->getPrice() : $listPrice = null;
        $calculatedPrice->getRegulationPrice() instanceof RegulationPrice ? $regulationPrice = $calculatedPrice->getRegulationPrice()->getPrice() : $regulationPrice = null;
        $definition = $this->buildDefinition($product, $calculatedPrice->getUnitPrice(), $salesChannelContext, $units, $reference, $quantity, $listPrice, $regulationPrice);
        return $this->calculator->calculate($definition, $salesChannelContext);
    }
}
