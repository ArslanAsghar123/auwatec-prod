<?php declare(strict_types=1);

namespace Intedia\Doofinder\Storefront\Controller;

use Intedia\Doofinder\Core\Content\Settings\Service\CommunicationHandler;
use Intedia\Doofinder\Core\Content\Settings\Service\SettingsHandler;
use Intedia\Doofinder\Doofinder\Api\Management;
use Intedia\Doofinder\Doofinder\Api\Search;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class DoofinderController extends StorefrontController
{
    protected $validCredentials = array(
        'intedia' => 'YxTnjKqZVneuXL6Np3RJHFfjjH8YbbNdwYxiU9X3',
        'doofinder' => 'FupVoy3HbByPea9ncEV7PWnYzCwgoJ4gUnKVtk9e'
    );

    public function __construct(
        private readonly EntityRepository                  $productExportRepository,
        private readonly SeoUrlPlaceholderHandlerInterface $seoUrlReplacer,
        private readonly SettingsHandler                   $settingsHandler,
        private readonly Search                            $searchApi,
        private readonly EntityRepository                  $productRepository,
        private readonly CommunicationHandler              $communicationHandler,
        private readonly Management                        $management
    ) {
    }

    #[Route(path: '/doofinder/config', name: 'frontend.doofinder.config', options:['seo' => false], methods: ['GET'])]
    public function config(SalesChannelContext $context, Request $request): Response
    {
        if ($this->validateIp() && $this->validateAuth($request)) {

            $responseData = [
                'platform' => [
                    'name' => 'Shopware 6'
                ],
                'module' => [
                    'version' => '2.3.2',
                    'configuration' => $this->getConfigurations($context)
                ]
            ];

            return new JsonResponse($responseData);
        }

        $response = new Response('Not authorized', 401);
        $response->headers->set('WWW-Authenticate', 'Basic realm="IntediaDoofinder"');

        return $response;
    }

    #[\Symfony\Component\Routing\Attribute\Route(path: '/doofinder/test', name: 'frontend.doofinder.test', options:['seo' => false], methods: ['GET'])]
    public function test(SalesChannelContext $context, Request $request): Response
    {
        if ($this->validateIp() && $this->validateAuth($request)) {

            $responseData = [
                'platform' => [
                    'name' => 'Shopware 6'
                ],
                'module' => [
                    'version' => '2.3.2',
                    'configuration' => $this->getApiTests($request, $context)
                ]
            ];

            return new JsonResponse($responseData);
        }

        $response = new Response('Not authorized', 401);
        $response->headers->set('WWW-Authenticate', 'Basic realm="IntediaDoofinder"');

        return $response;
    }

    protected function searchTest($term, SalesChannelContext $context)
    {
        return $this->searchApi->queryIds($term, $context);
    }

    protected function searchProducts($doofinderIds)
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new OrFilter([
                new EqualsAnyFilter('productNumber', array_values($doofinderIds)),
                new EqualsAnyFilter('parent.productNumber', array_keys($doofinderIds)),
                new EqualsAnyFilter('productNumber', array_keys($doofinderIds))
            ])
        )->setLimit(count($doofinderIds));
        $criteria->setOffset(0);

        return $this->productRepository->search($criteria, $this->settingsHandler->getContext());
    }

    protected function getConfigurations(SalesChannelContext $context): array
    {
        $feedUrls = [];

        foreach ($this->settingsHandler->getDooFinderChannels() as $dooFinderChannel) {

            $doofinderExport  = $dooFinderChannel->getProductExports()->first();
            $domain           = $doofinderExport->getSalesChannelDomain();

            $key = "{$doofinderExport->getStorefrontSalesChannel()->getName()} - {$domain->getLanguage()->getLocale()->getCode()}";

            $feedUrls[$key] = $this->getDooFinderChannelConfiguration($dooFinderChannel, $context);
        }

        return $feedUrls;
    }

    protected function getApiTests(Request $request, SalesChannelContext $context): array
    {
        $feedUrls = [];

        $test = $request->get('articleName');

        $i = 0;
        /** @var SalesChannelEntity $dooFinderChannel */
        $dooFinderChannel = $this->settingsHandler->getDooFinderChannel($this->settingsHandler->getDomain($context->getDomainId()));
        if (!$dooFinderChannel) {
            return ['error' => 'No DooFinder Channel Found'];
        }
            $doofinderExport = $dooFinderChannel->getProductExports()->first();
            $domain          = $doofinderExport->getSalesChannelDomain();
            $doofinderLayer  = $this->settingsHandler->getDoofinderLayer($domain);

            if ($doofinderLayer) {
                $feedUrls['layer'] = $doofinderLayer;

                if ($doofinderLayer->getDoofinderStoreId()) {
                    $feedUrls['api']['store'] = $this->communicationHandler->doofinderStore($doofinderLayer->getDoofinderStoreId());
                    $feedUrls['api']['searchIndex'] = $this->management->getProcessDoofinderSearchIndex($doofinderLayer->getDooFinderHashId());
                    $feedUrls['api']['searchEngine'] = $this->management->getSearchEngine($doofinderLayer->getDooFinderHashId());
                }

                $doofinderIds = $this->searchTest($test, $context);

                $feedUrls['searchResultIds'] = $doofinderIds;
                /** @var ProductEntity $product */
                foreach ($this->searchProducts($doofinderIds) as $product) {
                    $productName = $product->getName();

                    if (empty($product->getName())) {
                        $c = new Criteria([$product->getParentId()]);
                        $p = $this->productRepository->search($c, $this->settingsHandler->getContext());
                        $productName = $p->getEntities()->first()->getName();
                    }
                    $feedUrls['searchResults'][$product->getProductNumber()] = $productName;
                }
            }

        return $feedUrls;
    }

    protected function getDooFinderChannelConfiguration(SalesChannelEntity $dooFinderChannel, SalesChannelContext $context): array
    {
        $doofinderFeedChannel = $dooFinderChannel->getProductExports()->first();

        $seoUrl = $this->seoUrlReplacer->generate('store-api.product.export', [
            'accessKey' => $doofinderFeedChannel->getAccessKey(),
            'fileName'  => $doofinderFeedChannel->getFileName()
        ]);

        return [
            'feed'       => $this->seoUrlReplacer->replace($seoUrl, $doofinderFeedChannel->getSalesChannelDomain()->getUrl(), $context),
            'language'   => $doofinderFeedChannel->getSalesChannelDomain()->getLanguage()->getLocale()->getCode(),
            'currency'   => $doofinderFeedChannel->getSalesChannelDomain()->getCurrency()->getIsoCode(),
            'accessHash' => $doofinderFeedChannel->getAccessKey()
        ];
    }

    protected function validateAuth(Request $request): bool
    {
        $user = $request->headers->get('PHP_AUTH_USER');
        $pass = $request->headers->get('PHP_AUTH_PW');

        return array_key_exists($user, $this->validCredentials) && $pass == $this->validCredentials[$user];
    }

    protected function validateIp(): bool
    {
        return true; // in_array($this->getRequestIp(), [ '54.171.4.216', '52.2.218.41', '95.223.165.204' ]);
    }

    protected function getRequestIp(): string
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR']    = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }
}
