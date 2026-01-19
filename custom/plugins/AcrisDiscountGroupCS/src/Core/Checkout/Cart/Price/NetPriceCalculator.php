<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Checkout\Cart\Price;
use Acris\DiscountGroup\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig as AcrisRoundingConfig;
use Acris\DiscountGroup\Storefront\Subscriber\ChangeDecimalPlacesSubscriber;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ListPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\RegulationPrice;
use Shopware\Core\Checkout\Cart\Tax\TaxCalculator;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Cart\Price\NetPriceCalculator as ParentClass;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;

class NetPriceCalculator extends ParentClass
{
    public function __construct(
        private readonly TaxCalculator $taxCalculator,
        private readonly CashRounding $priceRounding,
        private readonly ParentClass $parent
    ) {
        parent::__construct($taxCalculator,$priceRounding);
    }

    public function calculate(QuantityPriceDefinition $definition, CashRoundingConfig $config): CalculatedPrice
    {
        $isAcrisRoundingConfig = ($config instanceof AcrisRoundingConfig);

        if(!$isAcrisRoundingConfig) {
            return parent::calculate($definition,$config);
        }

        $unitPrice = $this->round($definition->getPrice(), $config);

        $taxRules = $definition->getTaxRules();

        $calculatedTaxes = $this->taxCalculator->calculateNetTaxes(
            $unitPrice,
            $definition->getTaxRules()
        );

        $orgDecimals = $config->getOriginalDecimals();
        $config->setDecimals($orgDecimals);

        foreach ($calculatedTaxes as $tax) {
            $total = $this->priceRounding->mathRound(
                $tax->getTax() * $definition->getQuantity(),
                $config
            );
            $tax->setTax($total);
            $tax->setPrice($tax->getPrice() * $definition->getQuantity());
        }

        $price = $this->round(
            $unitPrice * $definition->getQuantity(),
            $config
        );

        $config->setDecimals(ChangeDecimalPlacesSubscriber::INCREASED_DECIMAL_PLACES);

        $reference = $this->calculateReferencePrice($unitPrice, $definition->getReferencePriceDefinition(), $config);

        return new CalculatedPrice(
            $unitPrice,
            $price,
            $calculatedTaxes,
            $taxRules,
            $definition->getQuantity(),
            $reference,
            $this->calculateListPrice($unitPrice, $definition, $config),
            $this->calculateRegulationPrice($definition, $config)
        );
    }

    private function calculateListPrice(float $unitPrice, QuantityPriceDefinition $definition, CashRoundingConfig $config): ?ListPrice
    {
        $listPrice = $definition->getListPrice();
        if (!$listPrice) {
            return null;
        }

        if (!$definition->isCalculated()) {
            $listPrice = $this->round($listPrice, $config);
        }

        return ListPrice::createFromUnitPrice($unitPrice, $listPrice);
    }

    private function calculateRegulationPrice(QuantityPriceDefinition $definition, CashRoundingConfig $config): ?RegulationPrice
    {
        $regulationPrice = $definition->getRegulationPrice();
        if (!$regulationPrice) {
            return null;
        }

        if (!$definition->isCalculated()) {
            $regulationPrice = $this->round($regulationPrice, $config);
        }

        return new RegulationPrice($regulationPrice);
    }

    private function calculateReferencePrice(float $price, ?ReferencePriceDefinition $definition, CashRoundingConfig $config): ?ReferencePrice
    {
        if (!$definition) {
            return null;
        }

        if ($definition->getPurchaseUnit() <= 0 || $definition->getReferenceUnit() <= 0) {
            return null;
        }

        $price = $price / $definition->getPurchaseUnit() * $definition->getReferenceUnit();

        $price = $this->priceRounding->mathRound($price, $config);

        return new ReferencePrice(
            $price,
            $definition->getPurchaseUnit(),
            $definition->getReferenceUnit(),
            $definition->getUnitName()
        );
    }

    private function round(float $price, CashRoundingConfig $config): float
    {
        if ($config->roundForNet()) {
            return $this->priceRounding->cashRound($price, $config);
        }

        return $this->priceRounding->mathRound($price, $config);
    }
}