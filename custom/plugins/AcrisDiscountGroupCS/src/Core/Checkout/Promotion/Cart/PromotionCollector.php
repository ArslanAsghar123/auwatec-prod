<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Checkout\Promotion\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Promotion\Cart\PromotionCollector as ParentClass;

class PromotionCollector extends ParentClass
{
    public const CONTEXT_PREVENT_DISCOUNT_GROUP_KEY = 'acrisDiscountGroupPrevent';
    private CartDataCollectorInterface $parent;

    public function __construct(
        CartDataCollectorInterface $parent
    )
    {
        $this->parent = $parent;
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->parent->collect($data, $original, $context, $behavior);

        $promotionItems = null;
        if ($data->has(PromotionProcessor::DATA_KEY)) {
            $promotionItems = $data->get(PromotionProcessor::DATA_KEY);
        }

        if (empty($promotionItems) || !$promotionItems instanceof LineItemCollection || $promotionItems->count() === 0) {
            $this->assignPreventDiscountGroup($context, false);
            return;
        }

        $this->checkPromotionPreventDiscountGroup($promotionItems, $context);
    }

    private function checkPromotionPreventDiscountGroup(LineItemCollection $promotionItems, SalesChannelContext $context): void
    {
        $preventDiscountGroup = false;
        /** @var LineItem $promotionItem */
        foreach ($promotionItems as $promotionItem) {
            if ($promotionItem->hasPayloadValue(PromotionItemBuilder::PROMOTION_PREVENT_DISCOUNT_GROUP_KEY) && !empty($promotionItem->getPayloadValue(PromotionItemBuilder::PROMOTION_PREVENT_DISCOUNT_GROUP_KEY)) && $promotionItem->getPayloadValue(PromotionItemBuilder::PROMOTION_PREVENT_DISCOUNT_GROUP_KEY) === true) $preventDiscountGroup = true;
        }

        $this->assignPreventDiscountGroup($context, $preventDiscountGroup);
    }

    private function assignPreventDiscountGroup(SalesChannelContext $context, bool $preventDiscountGroup): void
    {
        if ($preventDiscountGroup) {
            $context->addExtension(self::CONTEXT_PREVENT_DISCOUNT_GROUP_KEY, new ArrayEntity(['prevent' => true]));
        } else {
            if ($context->hasExtension(self::CONTEXT_PREVENT_DISCOUNT_GROUP_KEY)) {
                $context->removeExtension(self::CONTEXT_PREVENT_DISCOUNT_GROUP_KEY);
            }
        }
    }
}
