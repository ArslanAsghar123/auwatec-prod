<?php
namespace LoyxxCookiePopupWithFooterIntegration\Subscribers;

use LoyxxCookiePopupWithFooterIntegration\Struct\ConfigStruct;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontPageLoadedEvent implements EventSubscriberInterface
{

    /**
     * @var SystemConfigService
     */
    private $configService;

    public const CONFIG_CATEGORY_VALUE = 'LoyxxCookiePopupWithFooterIntegration';

    public function __construct(SystemConfigService $configService)
    {
        $this->configService = $configService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender',
        ];
    }

    public function onStorefrontRender(StorefrontRenderEvent $event)
    {
        $enabled = $this->configService->get('LoyxxCookiePopupWithFooterIntegration.config.loyxxEnableCookieSettingsReload') ?? false;
        $position = $this->configService->get('LoyxxCookiePopupWithFooterIntegration.config.loyxxCookieSettingsLinkPosition') ?? 'button';

        $configStruct = new ConfigStruct([
            'loyxx_cookie_enabled' => $enabled,
            'loyxx_cookie_button_pos' => $position,
            'loyxx_category_id' => md5(self::CONFIG_CATEGORY_VALUE)
        ]);
        $event->getContext()->addExtension('loyxx_configrations', $configStruct);
    }
}