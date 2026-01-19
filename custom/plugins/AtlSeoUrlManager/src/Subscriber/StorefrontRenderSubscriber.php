<?php declare(strict_types=1);

namespace Atl\SeoUrlManager\Subscriber;

use Shopware\Core\Content\Seo\HreflangLoaderInterface;
use Shopware\Core\Content\Seo\HreflangLoaderParameter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Storefront\Page\Product\ProductPage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontRenderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly HreflangLoaderInterface $hreflangLoader
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }

    public function onStorefrontRender(StorefrontRenderEvent $event): void
    {
        $route = $event->getRequest()->attributes->get('_route');

        if ($route !== 'frontend.detail.page' || \count($event->getParameters()['hrefLang']) < 1) {
            return;
        }

        $parentCanonicalUrlsConfig = $this->systemConfigService->get('AtlSeoUrlManager.config.parentCanonicalUrls');

        if (!$parentCanonicalUrlsConfig) {
            return;
        }

        $page = $event->getParameters()['page'];
        if (!$page instanceof ProductPage) {
            return;
        }

        $productParentId = $page->getProduct()->getParentId();

        if (empty($productParentId)) {
            return;
        }

        $routeParams = $event->getRequest()->attributes->get('_route_params', []);
        $routeParams['productId'] = $productParentId;
        $parameter = new HreflangLoaderParameter($route, $routeParams, $event->getSalesChannelContext());

        $hreflangCollection = $this->hreflangLoader->load($parameter);

        $event->setParameter('hrefLang', $hreflangCollection);
    }
}
