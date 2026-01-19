<?php declare(strict_types=1);

namespace DreiscSeoPro\Controller;

use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RobotsTxtStruct;
use OpenAI;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use DreiscSeoFilter\Core\DreiscSeoFilter\Page\SeoFilterPageLoader;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class RobotsTxtController extends StorefrontController
{
    public function __construct(
        private readonly CustomSettingLoader $customSettingLoader
    ) { }

    #[\Symfony\Component\Routing\Attribute\Route(path: '/robots.txt', name: 'frontend.dreisc_seo.robots', methods: ['GET'])]
    public function showRobots(Request $request, SalesChannelContext $context): Response
    {
        /** Load the custom settings */
        $customSettings = $this->customSettingLoader->load($context->getSalesChannelId(), true);
        $robotsTxtContent = $customSettings->getRobotsTxt()->getContent();
        $robotsTxtAddSitemap = $customSettings->getRobotsTxt()->getAddSitemap();

        if (empty($robotsTxtContent)) {
            $robotsTxtContent = RobotsTxtStruct::CONTENT_DEFAULT;
        }

        if ($robotsTxtAddSitemap) {
            $sitemapUrl = sprintf(
                '%s%s/sitemap.xml',
                $request->attributes->get('sw-sales-channel-absolute-base-url'),
                $request->attributes->get('sw-sales-channel-base-url')
            );

            $robotsTxtContent .= sprintf(
                "\n\nSitemap: %s",
                $sitemapUrl
            );
        }


        return new Response(
            $robotsTxtContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/plain'
            ]
        );
    }
}
