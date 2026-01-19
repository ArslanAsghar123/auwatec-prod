<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Storefront\Subscriber;

use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartConvertedSubscriber implements EventSubscriberInterface
{
    const CART_COLUMN_LAYOUT_DEFAULT = 'default';
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(
        SystemConfigService $systemConfigService
    ) {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CartConvertedEvent::class => 'onCartConverted'
        ];
    }

    public function onCartConverted(CartConvertedEvent $event): void
    {
        $convertedCart = $event->getConvertedCart();

        $cartColumnLayoutConfig = $this->systemConfigService->get('AcrisDiscountGroupCS.config.cartColumnLayout', $event->getSalesChannelContext()->getSalesChannelId());

        if (!$cartColumnLayoutConfig) {
            $cartColumnLayoutConfig = self::CART_COLUMN_LAYOUT_DEFAULT;
        }

        if (!array_key_exists('customFields', $convertedCart) || !array_key_exists('acrisCartColumnLayout', $convertedCart['customFields'])) {
            $convertedCart['customFields']['acrisCartColumnLayout'] =  $cartColumnLayoutConfig;
        }

        $event->setConvertedCart($convertedCart);
    }
}
