<?php declare(strict_types=1);

namespace phpSchmied\LastSeenProducts\Controller;

use phpSchmied\LastSeenProducts\Service\LastSeenProductService;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class LastSeenProductController extends StorefrontController
{
    private SystemConfigService $systemConfigService;
    private LastSeenProductService $lastSeenProductService;

    /**
     * @param SystemConfigService $systemConfigService
     * @param LastSeenProductService $lastSeenProductService
     */
    public function __construct(
        SystemConfigService    $systemConfigService,
        LastSeenProductService $lastSeenProductService
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->lastSeenProductService = $lastSeenProductService;
    }

    #[Route(path: '/lastSeenProducts/add', name: 'frontend.lastSeenProducts.add', defaults: ['csrf_protected' => false, 'XmlHttpRequest' => true], methods: ['POST'])]
    public function add(Request $request, SalesChannelContext $context): Response
    {
        $productId = $request->get('productId');
        $session = $request->getSession();

        $lastSeenArray = [];
        if ($productId !== null) {
            $lastSeenArray = $this->lastSeenProductService->addLastSeenProductId($productId, $session, $context);
        }
        $session->set('lastSeenProductIds', $lastSeenArray);

        return new NoContentResponse();
    }

    #[Route(path: '/lastSeenProducts', name: 'frontend.lastSeenProducts.fetch', defaults: ['XmlHttpRequest' => true], methods: ['GET'])]
    public function fetch(Request $request, SalesChannelContext $context): Response
    {
        $session = $request->getSession();
        $cms = $request->get('cms') !== null;
        $tabRequest = $request->get('tab') !== null;
        $header = $request->get('header') !== null;
        $active = $request->get('active');
        $layout = $request->get('layout', 'standard');
        $displayMode = $request->get('displayMode', 'standard');
        $productId = $request->get('productId');

        $tabConfig = $this->systemConfigService->get(
            'phpSchmiedLastSeenProducts.config.useAsTab',
            $context->getSalesChannelId()
        );

        if($tabRequest && !$tabConfig) {
            return new NoContentResponse();
        }

        $lastSeenProducts = $this->lastSeenProductService->getLastSeenProductCollection($session, $context, $productId ?? '');

        if($lastSeenProducts->count() <= 0) {
            return new NoContentResponse();
        }

        if($header) {
            return $this->renderStorefront('@phpSchmiedLastSeenProducts/storefront/element/cms-element-cross-selling-header.html.twig', [
                'product' => $productId,
                'active' => $active,
            ]);
        }

        if($tabRequest) {
            return $this->renderStorefront('@phpSchmiedLastSeenProducts/storefront/element/cms-element-cross-selling-tab.html.twig', [
                'products' => $lastSeenProducts,
                'product' => $productId
            ]);
        }

        if ($cms === false) {
            return $this->renderStorefront('@phpSchmiedLastSeenProducts/storefront/component/last-seen/slider.html.twig', [
                'products' => $lastSeenProducts
            ]);
        }

        if ($cms === true) {
            return $this->renderStorefront('@phpSchmiedLastSeenProducts/storefront/component/product-slider.html.twig', [
                'products' => $lastSeenProducts,
                'layout' => $layout,
                'displayMode' => $displayMode
            ]);
        }

        return new NoContentResponse();
    }
}
