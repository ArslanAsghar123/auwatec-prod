<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Content\Product\Cart;

use Acris\DiscountGroup\Components\ScaleDiscountService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Content\Product\Cart\ProductCartProcessor as ParentClass;

class ProductCartProcessor extends ParentClass
{
    public const DISCOUNT_GROUP_PROMOTION_PREVENT = 'acrisDiscountGroupPromotionPrevent';

    private ParentClass $parent;

    private AbstractProductPriceCalculator $priceCalculator;

    private ScaleDiscountService $scaleDiscountService;

    public function __construct(
        ParentClass $parent,
        AbstractProductPriceCalculator $priceCalculator,
        ScaleDiscountService $scaleDiscountService
    ) {
        $this->parent = $parent;
        $this->priceCalculator = $priceCalculator;
        $this->scaleDiscountService = $scaleDiscountService;
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->markLineItemsAsModified($original);

        $this->parent->collect($data, $original, $context, $behavior);

        $lineItems = $this->getProducts($original->getLineItems());

        $this->addDiscountGroupPromotionPreventExtension($lineItems, $data);

        $this->scaleDiscountService->checkScaleDiscounts($original->getLineItems(), $data, $context);
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->parent->process($data, $original, $toCalculate, $context, $behavior);
    }

    private function getProducts(LineItemCollection $items): array
    {
        $matches = [];
        foreach ($items as $item) {
            if ($item->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $matches[] = ['item' => $item, 'scope' => $items];
            }

            $nested = $this->getProducts($item->getChildren());

            foreach ($nested as $match) {
                $matches[] = $match;
            }
        }

        return $matches;
    }

    private function getDataKey(string $id): string
    {
        return 'product-' . $id;
    }

    private function addDiscountGroupPromotionPreventExtension(array $lineItems, CartDataCollection $data): void
    {
        foreach ($lineItems as $match) {
            if(!isset($match['item'])) {
                continue;
            }
            $lineItem = $match['item'];

            $id = $lineItem->getReferencedId();

            $product = $data->get(
                $this->getDataKey((string) $id)
            );

            // no data for enrich exists
            if (!$product instanceof SalesChannelProductEntity) {
                continue;
            }
            $product->addExtension(self::DISCOUNT_GROUP_PROMOTION_PREVENT, new ArrayEntity(['prevent' => true]));
        }
    }

    private function markLineItemsAsModified(Cart $cart) : void
    {
        $lineItems = $cart->getLineItems()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);

        foreach ($lineItems as $lineItem) {
            $lineItem->markModified();
        }

    }
}
