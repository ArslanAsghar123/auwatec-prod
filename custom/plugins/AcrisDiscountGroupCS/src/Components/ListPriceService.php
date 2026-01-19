<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components;

use Acris\DiscountGroup\Core\Checkout\Cart\Price\Struct\AcrisListPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ListPriceService
{
    private PriceRoundingService $priceRoundingService;

    private SystemConfigService $systemConfigService;

    public function __construct( PriceRoundingService $priceRoundingService, SystemConfigService $systemConfigService )
    {
        $this->priceRoundingService = $priceRoundingService;
        $this->systemConfigService = $systemConfigService;
    }

    public function calculateProductListPrices(iterable $products, SalesChannelContext $salesChannelContext): void
    {
        $roundingType = $this->systemConfigService->getString('AcrisDiscountGroupCS.config.typeOfRounding', $salesChannelContext->getSalesChannelId() );
        $decimalPlaces = intval( $this->systemConfigService->get('AcrisDiscountGroupCS.config.decimalPlaces', $salesChannelContext->getSalesChannelId() ) );

        /** @var Entity $product */
        foreach( $products as $product )
        {
            foreach ($product->get("calculatedPrices") as $key => $calculatedPrice) {
                $newCalculatedPrice = $this->roundCalculatedPrice($calculatedPrice, $decimalPlaces, $roundingType);
                $calculatedPrices = $product->get("calculatedPrices");
                $calculatedPrices->set($key, $newCalculatedPrice);
            }

            $calculatedPrice = $product->get("calculatedPrice");
            if(!$calculatedPrice instanceof CalculatedPrice) continue;
            $newCalculatedPrice = $this->roundCalculatedPrice($calculatedPrice, $decimalPlaces, $roundingType);
            $product->assign(["calculatedPrice" => $newCalculatedPrice]);
        }
    }

    private function roundCalculatedPrice(CalculatedPrice $calculatedPrice, int $decimalPlaces, string $roundingType): CalculatedPrice
    {
        if( $listPrice = $calculatedPrice->getListPrice() )
        {
            $percentageRounded = $this->priceRoundingService->round( $listPrice->getPercentage(), $decimalPlaces, $roundingType );
            $listPriceNew = new AcrisListPrice( $listPrice->getPrice(), $listPrice->getDiscount(), $percentageRounded );
            $newCalculatedPrice = new CalculatedPrice(
                $calculatedPrice->getUnitPrice(),
                $calculatedPrice->getTotalPrice(),
                $calculatedPrice->getCalculatedTaxes(),
                $calculatedPrice->getTaxRules(),
                $calculatedPrice->getQuantity(),
                $calculatedPrice->getReferencePrice(),
                $listPriceNew,
                $calculatedPrice->getRegulationPrice()
            );
            $newCalculatedPrice->setExtensions($calculatedPrice->getExtensions());
            $calculatedPrice = $newCalculatedPrice;
        }
        return $calculatedPrice;
    }
}
