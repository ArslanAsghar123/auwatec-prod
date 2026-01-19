<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Storefront\Subscriber;

use Acris\DiscountGroup\Components\DiscountGroupGateway;
use Acris\DiscountGroup\Components\DiscountGroupService;
use Acris\DiscountGroup\Components\Struct\LineItemDiscountGroupData;
use Acris\DiscountGroup\Custom\DiscountGroupEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiscountGroupSubscriber implements EventSubscriberInterface
{
    const DISCOUNT_GROUP_WRITTEN_EVENT = 'acris_discount_group.written';

    private CartService $cartService;
    private DiscountGroupGateway $discountGroupGateway;
    private SystemConfigService $systemConfigService;

    public function __construct(
        CartService $cartService,
        DiscountGroupGateway $discountGroupGateway,
        SystemConfigService $systemConfigService
    ) {
        $this->cartService = $cartService;
        $this->discountGroupGateway = $discountGroupGateway;
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded'
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        if ($this->systemConfigService->get('AcrisDiscountGroupCS.config.activateAcrossScaleDiscounts', $salesChannelContext->getSalesChannel()->getId()) !== true || $this->systemConfigService->get('AcrisDiscountGroupCS.config.showCartQuantityInfoDetail', $salesChannelContext->getSalesChannel()->getId()) !== 'aboveScalePrices') {
            return;
        }

        $product = $event->getPage()->getProduct();
        $cart = $this->cartService->getCart($event->getSalesChannelContext()->getToken(), $event->getSalesChannelContext());

        $lineItem = $cart->getLineItems()->filter(function(LineItem $item) use ($product) {
           return $item->getReferencedId() === $product->getId();
        })->first();

        if (empty($lineItem) || !$lineItem instanceof LineItem) {
            $lineItem = $this->loadLineItemWithSameDiscountGroup($cart, $product, $salesChannelContext);
        }

        if (empty($lineItem) || !$lineItem instanceof LineItem) {
            return;
        }

        list($id, $name, $quantity) = $this->loadDiscountGroupDataFromPayload($lineItem->getPayload());

        if (!is_int($quantity)) {
            return;
        }

        $discountGroupData = new LineItemDiscountGroupData($id, $name, $quantity);

        $product->addExtension('acrisDiscountGroupLineItemData', $discountGroupData);
    }

    private function loadDiscountGroupDataFromPayload(?array $payload): array
    {
        $id = null;
        $name = null;
        $quantity = null;

        if (empty($payload) || !is_array($payload)) {
            return [$id, $name, $quantity];
        }

        if (array_key_exists('discountGroupScalePriceId', $payload)) {
            $id = $payload['discountGroupScalePriceId'];
        }

        if (array_key_exists('discountGroupScalePriceDisplayName', $payload)) {
            $name = $payload['discountGroupScalePriceDisplayName'];
        }

        if (array_key_exists('discountGroupScalePriceQuantity', $payload)) {
            $quantity = $payload['discountGroupScalePriceQuantity'];
        }

        return [$id, $name, $quantity];
    }

    private function loadLineItemWithSameDiscountGroup(Cart $cart, SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): ?LineItem
    {
        if ($cart->getLineItems()->count() === 0) {
            return null;
        }

        if ($product->hasExtension(DiscountGroupService::ACRIS_STREAM_IDS_EXTENSION) && !empty($product->getExtension(DiscountGroupService::ACRIS_STREAM_IDS_EXTENSION))) {
            $productStreamIds = $product->getExtension(DiscountGroupService::ACRIS_STREAM_IDS_EXTENSION)->get('ids');
        } else {
            $productStreamIds = $this->discountGroupGateway->getProductStreamIds([$product->getId()], $salesChannelContext->getContext());
            $product->addExtension(DiscountGroupService::ACRIS_STREAM_IDS_EXTENSION, new ArrayEntity(['ids' => $productStreamIds]));
        }

        $discountGroupResult = $this->discountGroupGateway->getAllDiscountGroupsForProduct($salesChannelContext, $product->getId(), $productStreamIds);

        if ($discountGroupResult->getTotal() === 0) {
            return null;
        }

        /** @var DiscountGroupEntity $discountGroup */
        foreach ($discountGroupResult->getEntities()->getElements() as $discountGroup) {
            $lineItem = $cart->getLineItems()->filter(function(LineItem $item) use ($discountGroup) {
                return $item->hasPayloadValue('discountGroupScalePriceId') && !empty($item->getPayloadValue('discountGroupScalePriceId')) && $item->getPayloadValue('discountGroupScalePriceId') === $discountGroup->getId();
            })->first();

            if (!empty($lineItem) && $lineItem instanceof LineItem) {
                return $lineItem;
            }
        }

        return null;
    }
}
