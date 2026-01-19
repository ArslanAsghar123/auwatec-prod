<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Routing;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Core\Routing\Category\CategoryRedirectSearcher;
use DreiscSeoPro\Core\Routing\Product\ProductRedirectSearcher;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\HttpFoundation\Request;

class RequestTransformer
{
    final public const SALES_CHANNEL_BASE_URL = 'sw-sales-channel-base-url';

    public function __construct(
        private readonly DreiscSeoRedirectRepository $dreiscSeoRedirectRepository,
        private readonly RedirectExecutor $redirectExecutor,
        private readonly ProductRedirectSearcher $productRedirectSearcher,
        private readonly CategoryRedirectSearcher $categoryRedirectSearcher,
        private readonly RedirectFinder $redirectFinder
    ) { }

    /**
     * This method will be run after the sales channel and the seo url information was fetched
     * by the decorated class
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function transform(Request $shopwareRequest, Request $request): Request
    {
        /** Abort, if it's not a sales channel url */
        if(true !== $shopwareRequest->attributes->has(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST)) {
            return $shopwareRequest;
        }

        /** Check for entity redirects */
        $this->checkForEntityRedirects($shopwareRequest);

        /** Fetch the path info */
        $pathInfo = $this->getAdjustedRequestUri($request, $shopwareRequest);

        /** Try to fetch a redirect for this path */
        $dreiscSeoRedirectEntity = $this->redirectFinder->findBySourceUrl(
            $pathInfo,
            //$shopwareRequest,
            $shopwareRequest->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID)
        );

        /** Abort, if there is no redirect */
        if (!$dreiscSeoRedirectEntity instanceof DreiscSeoRedirectEntity) {
            return $shopwareRequest;
        }

        /** Check, if this is a test run for PHPUnit */
        $isPhpUnitTest = !empty($request->server->get('IS_PHP_UNIT_TEST'));

        /** Execute the redirect */
        $this->redirectExecutor->redirect(
            $dreiscSeoRedirectEntity,
            $shopwareRequest->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID),
            $isPhpUnitTest
        );

        return $shopwareRequest;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function checkForEntityRedirects(Request $shopwareRequest): bool
    {
        $pathInfoExplode = explode('/', (string) $shopwareRequest->getPathInfo());

        /** Abort, if path info has more or less than three parts */
        if (empty($pathInfoExplode) || 3 !== count($pathInfoExplode)) {
            return false;
        }

        if (empty($pathInfoExplode[0]) && 'detail' === $pathInfoExplode[1] && !empty($pathInfoExplode[2])) {
            /** Check if there is redirect for the product */
            $this->productRedirectSearcher->search(
                $shopwareRequest,
                $pathInfoExplode[2]
            );

            return true;
        }

        if (empty($pathInfoExplode[0]) && 'navigation' === $pathInfoExplode[1] && !empty($pathInfoExplode[2])) {
            /** Check if there is redirect for the product */
            $this->categoryRedirectSearcher->search(
                $shopwareRequest,
                $pathInfoExplode[2]
            );

            return true;
        }

        return false;
    }

    private function getAdjustedRequestUri(Request $request, Request $shopwareRequest): bool|string
    {
        $adjustedRequestUri = $request->getRequestUri();
        
        /**
         * Remove the base url from the beginning if available
         */
        $baseUrl = $shopwareRequest->attributes->get(self::SALES_CHANNEL_BASE_URL);
        if (empty($baseUrl)) {
            $absoluteBaseUrl = $shopwareRequest->attributes->get('sw-sales-channel-absolute-base-url');
            $absoluteBaseUrl = ltrim($absoluteBaseUrl, 'https://');
            $absoluteBaseUrl = ltrim($absoluteBaseUrl, 'http://');
            $absoluteBaseUrl = explode('/', $absoluteBaseUrl);
            array_shift($absoluteBaseUrl);

            if(!empty($absoluteBaseUrl)) {
                $baseUrl = implode('/', $absoluteBaseUrl);
            }
        }

        if(!empty($baseUrl)) {
            /** Make sure, that there is a slash at the end of the base url */
            $baseUrl = rtrim((string) $baseUrl, '/') . '/';

            /** Remove the base url from the request uri */
            $adjustedRequestUri = substr((string) $adjustedRequestUri, strlen($baseUrl));

            /**
             * There are some special cases. For example, when $adjustedRequestUri is "/en"
             * and $baseUrl is "/en/". In this case $baseUrl is longer then $adjustedRequestUri
             * and substr will return false. In this case we set "/" as the new value
             */
            if(false === $adjustedRequestUri) {
                $adjustedRequestUri = '/';
            }
        }

        /** Make sure that there is no slash at the beginning */
        $adjustedRequestUri = ltrim((string) $adjustedRequestUri, '/');
        
        return $adjustedRequestUri;
    }
}
