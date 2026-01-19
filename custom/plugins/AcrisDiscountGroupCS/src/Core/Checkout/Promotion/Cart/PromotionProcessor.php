<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Checkout\Promotion\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Promotion\Exception\InvalidPriceDefinitionException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor as ParentClass;

#[Package('buyers-experience')]
class PromotionProcessor extends ParentClass
{
    public function __construct(
        private readonly CartProcessorInterface $parent
    ) {
    }

    /**
     * @throws CartException
     * @throws InvalidPriceDefinitionException
     */
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->parent->process($data, $original, $toCalculate, $context, $behavior);
        $this->checkPromotionPreventDiscountGroup($toCalculate->getLineItems(), $context);
    }

    private function checkPromotionPreventDiscountGroup(LineItemCollection $lineItems, SalesChannelContext $context): void
    {
        $preventDiscountGroup = false;

        if ($lineItems->count() === 0) {
            $this->assignPreventDiscountGroup($context, false);
            return;
        }

        /** @var LineItem $lineItem */
        foreach ($lineItems->getElements() as $lineItem) {
            if ($lineItem->hasPayloadValue(PromotionItemBuilder::PROMOTION_PREVENT_DISCOUNT_GROUP_KEY) && !empty($lineItem->getPayloadValue(PromotionItemBuilder::PROMOTION_PREVENT_DISCOUNT_GROUP_KEY)) && $lineItem->getPayloadValue(PromotionItemBuilder::PROMOTION_PREVENT_DISCOUNT_GROUP_KEY) === true) {
                $preventDiscountGroup = true;
                break;
            }
        }

        $this->assignPreventDiscountGroup($context, $preventDiscountGroup);
    }

    private function assignPreventDiscountGroup(SalesChannelContext $context, bool $preventDiscountGroup): void
    {
        if ($preventDiscountGroup) {
            $context->addExtension(PromotionCollector::CONTEXT_PREVENT_DISCOUNT_GROUP_KEY, new ArrayEntity(['prevent' => true]));
        } else {
            if ($context->hasExtension(PromotionCollector::CONTEXT_PREVENT_DISCOUNT_GROUP_KEY)) {
                $context->removeExtension(PromotionCollector::CONTEXT_PREVENT_DISCOUNT_GROUP_KEY);
            }
        }
    }
}
