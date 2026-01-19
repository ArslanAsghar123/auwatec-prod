<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Storefront\Subscriber;

use Acris\DiscountGroup\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ChangeDecimalPlacesSubscriber implements EventSubscriberInterface
{
    public const ORIGINAL_ROUNDING_EXTENSION_KEY = 'acrisOriginalRoundingConfig';

    public const INCREASED_DECIMAL_PLACES = 5;

    public const OPTION_INCREASE_NUMBER_OF_DECIMAL_PLACES = 'customPositionRoundigAndDisplay';

    public function __construct(private readonly SystemConfigService $systemConfigService)
    {

    }
    public static function getSubscribedEvents()
    {
        return [
            SalesChannelContextCreatedEvent::class => ['changeDecimalPlacesIfNeeded']
        ];
    }

    public function changeDecimalPlacesIfNeeded(SalesChannelContextCreatedEvent $event) : void
    {
        $context = $event->getContext();

        $cartRoundingType = $this->systemConfigService->get('AcrisDiscountGroupCS.config.cartRounding');

        if($cartRoundingType !== self::OPTION_INCREASE_NUMBER_OF_DECIMAL_PLACES) {
            return;
        }

        $clonedRoundingConfig = clone $context->getRounding();
        $originalRoundingConfig = $context->getRounding();
        $originalRoundingConfig->setDecimals(self::INCREASED_DECIMAL_PLACES);
        $context->setRounding($originalRoundingConfig);
        $context->addExtension(self::ORIGINAL_ROUNDING_EXTENSION_KEY,$clonedRoundingConfig);

        $salesChannel = $event->getSalesChannelContext();
        $itemRounding = $salesChannel->getItemRounding();

        $acrisItemRounding = new CashRoundingConfig(
            $itemRounding->getDecimals(),
            $itemRounding->getInterval(),
            $itemRounding->roundForNet(),
            $clonedRoundingConfig->getDecimals());

        $salesChannel->setItemRounding($acrisItemRounding);
    }
}