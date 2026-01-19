<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Content\Product\SalesChannel\Price;

use Acris\DiscountGroup\Components\DiscountGroupService;
use Acris\DiscountGroup\Components\ListPriceService;
use Acris\DiscountGroup\Core\Checkout\Promotion\Cart\PromotionCollector;
use Acris\DiscountGroup\Core\Content\Product\Cart\ProductCartProcessor;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Content\Product\SalesChannel\Price\ProductPriceCalculator as ParentClass;

class ProductPriceCalculator extends ParentClass
{
    /**
     * @var AbstractProductPriceCalculator
     */
    private $parent;
    /**
     * @var DiscountGroupService
     */
    private $discountGroupService;

    /**
     * @var ListPriceService
     */
    private $listPriceService;

    public function __construct(AbstractProductPriceCalculator $parent, DiscountGroupService $discountGroupService, ListPriceService  $listPriceService)
    {
        $this->parent = $parent;
        $this->discountGroupService = $discountGroupService;
        $this->listPriceService = $listPriceService;
    }

    public function getDecorated(): AbstractProductPriceCalculator
    {
        return $this->parent->getDecorated();
    }

    public function calculate(iterable $products, SalesChannelContext $context): void
    {
        $this->parent->calculate($products, $context);

        if ($this->preventDiscountGroup($context, $products) === true) return;

        $this->discountGroupService->calculateProductPrices($products, $context);

        $this->listPriceService->calculateProductListPrices( $products, $context );
    }

    private function preventDiscountGroup(SalesChannelContext $context, iterable $products): bool
    {
        $match = false;
        foreach ($products as $product) {
            if (!$product instanceof SalesChannelProductEntity) continue;
            if ($product->hasExtension(ProductCartProcessor::DISCOUNT_GROUP_PROMOTION_PREVENT)) {
                $match = true;
                $product->removeExtension(ProductCartProcessor::DISCOUNT_GROUP_PROMOTION_PREVENT);
            }
        }

        return $match && $context->hasExtension(PromotionCollector::CONTEXT_PREVENT_DISCOUNT_GROUP_KEY) && !empty($context->getExtension(PromotionCollector::CONTEXT_PREVENT_DISCOUNT_GROUP_KEY));
    }
}
