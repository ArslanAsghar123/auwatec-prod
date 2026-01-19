<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Routing;

use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEnum;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Content\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainRepository;
use DreiscSeoPro\Core\Content\SalesChannel\SalesChannelRepository;
use DreiscSeoPro\Core\Foundation\Seo\SeoUrlAssembler;
use DreiscSeoPro\Test\Core\Routing\RedirectExecutorTest;
use RuntimeException;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityCollection;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/** @see RedirectExecutorTest */
class RedirectExecutor
{
    /**
     * @var SalesChannelDomainRepository
     */
    private $salesChannelDomainRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param SalesChannelDomainRepository $salesChannelDomainRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(private readonly SalesChannelRepository $salesChannelRepository, SalesChannelDomainRepository $salesChannelDomainRepository, ProductRepository $productRepository, private readonly CategoryRepository $categoryRepository, private readonly SeoUrlAssembler $seoUrlAssembler)
    {
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Performs a redirect if the given redirect entity is valid
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function redirect(DreiscSeoRedirectEntity $dreiscSeoRedirectRepositoryEntity, string $sourceSalesChannelDomainId): void
    {
        $statusCode = (int) $dreiscSeoRedirectRepositoryEntity->getRedirectHttpStatusCode();

        /** Fetch the redirect url */
        $redirectUrl = $this->getRedirect($dreiscSeoRedirectRepositoryEntity, $sourceSalesChannelDomainId);

        /** Abort, if the redirect url is null */
        if (null === $redirectUrl) {
            return;
        }

        /** Init the response */
        $response = new RedirectResponse($redirectUrl, $statusCode);

        /** Make sure that the redirect will not cached by the browser */
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->addCacheControlDirective('no-store');

        /** Execute the redirect */
        $response->send();
        eval('exit;');
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function getRedirect(DreiscSeoRedirectEntity $dreiscSeoRedirectRepositoryEntity, string $sourceSalesChannelDomainId): ?string
    {
        if (in_array($dreiscSeoRedirectRepositoryEntity->getRedirectType(), [
            DreiscSeoRedirectEnum::REDIRECT_TYPE__PRODUCT,
            DreiscSeoRedirectEnum::REDIRECT_TYPE__CATEGORY
        ], true)) {
            return $this->getRedirectEntity($dreiscSeoRedirectRepositoryEntity, $sourceSalesChannelDomainId);
        }

        return $this->getRedirectUrl($dreiscSeoRedirectRepositoryEntity);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getRedirectEntity(DreiscSeoRedirectEntity $dreiscSeoRedirectEntity, string $sourceSalesChannelDomainId): ?string
    {
        /**
         * Fetch the sales channel and sales channel entity
         */
        /** Determine the sales channel domain */
        $sourceSalesChannelDomainEntity = $this->salesChannelDomainRepository->get($sourceSalesChannelDomainId);
        if(null === $sourceSalesChannelDomainEntity) {
            return null;
        }

        /** Determine the sales channel */
        $sourceSalesChannelEntity = $this->salesChannelRepository->getBySalesChannelDomainId($sourceSalesChannelDomainId);
        if(null === $sourceSalesChannelEntity) {
            return null;
        }

        /** Determine the deviating redirect sales channel domain, if defined */
        $deviatingRedirectSalesChannelDomainEntity = null;
        if (
            $dreiscSeoRedirectEntity->getHasDeviatingRedirectSalesChannelDomain() &&
            !empty($dreiscSeoRedirectEntity->getDeviatingRedirectSalesChannelDomainId())
        ) {
            $deviatingRedirectSalesChannelDomainEntity = $this->salesChannelDomainRepository->get(
                $dreiscSeoRedirectEntity->getDeviatingRedirectSalesChannelDomainId(),
                [ 'salesChannel' ]
            );
        }

        /** Define the redirect sales channel and sales channel domain */
        if(null === $deviatingRedirectSalesChannelDomainEntity) {
            $redirectSalesChannelEntity = $sourceSalesChannelEntity;
            $redirectSalesChannelDomainEntity = $sourceSalesChannelDomainEntity;
        } else {
            $redirectSalesChannelEntity = $deviatingRedirectSalesChannelDomainEntity->getSalesChannel();
            $redirectSalesChannelDomainEntity = $deviatingRedirectSalesChannelDomainEntity;
        }

        if(null === $redirectSalesChannelEntity) {
            throw new RuntimeException('Redirect sales channel could not found');
        }

        if(null === $redirectSalesChannelDomainEntity) {
            throw new RuntimeException('Redirect sales channel domain could not found');
        }

        /**
         * Fetch the redirect url
         */
        switch($dreiscSeoRedirectEntity->getRedirectType())
        {
            /** Redirect to a product */
            case DreiscSeoRedirectEnum::REDIRECT_TYPE__PRODUCT:
                /** Create a context which active inheritance to support variants */
                $context = Context::createDefaultContext();
                $context->setConsiderInheritance(true);

                $productEntity = $this->productRepository->get(
                    $dreiscSeoRedirectEntity->getRedirectProductId(),
                    [ 'visibilities' ],
                    $context
                );

                /** Abort, if product entity is null */
                if(null === $productEntity) {
                    return null;
                }

                /** Abort, if product entity is not active */
                if(false === $productEntity->getActive()) {
                    return null;
                }

                /** Abort, if the product is not visible in the target sales channel */
                if(false === $this->isProductVisibleInSalesChannel($productEntity->getVisibilities(), $redirectSalesChannelEntity)) {
                    return null;
                }

                /** Fetch the url info for the product */
                $seoInfo = $this->seoUrlAssembler->assemble($productEntity, $redirectSalesChannelEntity->getId(), $redirectSalesChannelDomainEntity->getLanguageId());

                /** Abort, if there is no absolute paths for the current domain */
                if(empty($seoInfo[SeoUrlAssembler::ABSOLUTE_PATHS][$redirectSalesChannelDomainEntity->getId()])) {
                    return null;
                }

                return $this->handleParameterForwarding(
                    $seoInfo[SeoUrlAssembler::ABSOLUTE_PATHS][$redirectSalesChannelDomainEntity->getId()],
                    $dreiscSeoRedirectEntity
                );

                break;

            /** Redirect to a category */
            case DreiscSeoRedirectEnum::REDIRECT_TYPE__CATEGORY:
                $categoryEntity = $this->categoryRepository->get($dreiscSeoRedirectEntity->getRedirectCategoryId());

                /** Abort, if product entity is null */
                if(null === $categoryEntity) {
                    return null;
                }

                /** Abort, if product entity is not active */
                if(false === $categoryEntity->getActive()) {
                    return null;
                }

                /** Abort, if the category is not available for the shop */
                if(false === $this->categoryRepository->hasInPath(
                    $dreiscSeoRedirectEntity->getRedirectCategoryId(),
                    $redirectSalesChannelEntity->getNavigationCategoryId()
                )) {
                    return null;
                }

                /** Fetch the url info for the product */
                $seoInfo = $this->seoUrlAssembler->assemble($categoryEntity, $redirectSalesChannelEntity->getId(), $redirectSalesChannelDomainEntity->getLanguageId());

                /** Abort, if there is no absolute paths for the current domain */
                if(empty($seoInfo[SeoUrlAssembler::ABSOLUTE_PATHS][$redirectSalesChannelDomainEntity->getId()])) {
                    return null;
                }

                return $this->handleParameterForwarding(
                    $seoInfo[SeoUrlAssembler::ABSOLUTE_PATHS][$redirectSalesChannelDomainEntity->getId()],
                    $dreiscSeoRedirectEntity
                );

                break;

            default:
                throw new RuntimeException(
                    'Unknown redirect entity type: ' . $dreiscSeoRedirectEntity->getRedirectType()
                );
        }
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getRedirectUrl(DreiscSeoRedirectEntity $dreiscSeoRedirectEntity): ?string
    {
        /**
         * Fetch the redirect url
         */
        switch($dreiscSeoRedirectEntity->getRedirectType())
        {
            /** Redirect to an internal url */
            case DreiscSeoRedirectEnum::REDIRECT_TYPE__URL:
                /** Fetch the redirect sales channel domain */
                $salesChannelDomain = $this->salesChannelDomainRepository->get(
                    $dreiscSeoRedirectEntity->getRedirectSalesChannelDomainId()
                );

                /** Cleanup the domain (make sure that there is a slash at the end) */
                $redirectDomain = rtrim((string) $salesChannelDomain->getUrl(), '/') . '/';

                return $this->handleParameterForwarding(
                    $redirectDomain . $dreiscSeoRedirectEntity->getRedirectPath(),
                    $dreiscSeoRedirectEntity
                );

            /** Redirect to an external url */
            case DreiscSeoRedirectEnum::REDIRECT_TYPE__EXTERNAL_URL:
                return $this->handleParameterForwarding(
                    $dreiscSeoRedirectEntity->getRedirectUrl(),
                    $dreiscSeoRedirectEntity
            );

            default:
                throw new RuntimeException(
                    'Unknown redirect url type: ' . $dreiscSeoRedirectEntity->getRedirectType()
                );
        }
    }

    private function handleParameterForwarding(?string $url, DreiscSeoRedirectEntity $dreiscSeoRedirectEntity): ?string
    {
        if (
            !$dreiscSeoRedirectEntity->getParameterForwarding() ||
            !$dreiscSeoRedirectEntity->hasExtension(RedirectFinder::QUERY_PARAMS)
        ) {
            return $url;
        }

        /** @var ArrayStruct $params */
        $params = $dreiscSeoRedirectEntity->getExtension(RedirectFinder::QUERY_PARAMS);

        if (is_string($url)) {
            $parsedUrlQuery = parse_url($url, PHP_URL_QUERY);
            if ($parsedUrlQuery) {
                parse_str($parsedUrlQuery, $parsedUrlQuery);

                /** Remove all params from the parsed url query in the params */
                foreach($parsedUrlQuery as $key => $value) {
                    if (isset($params[$key])) {
                        unset($params[$key]);
                    }
                }
            }
        }

        return sprintf(
            '%s%s%s',
            $url,
            !str_contains($url, '?') ? '?' : '&',
            http_build_query($params->all())
        );
    }

    private function isProductVisibleInSalesChannel(?ProductVisibilityCollection $visibilities, ?SalesChannelEntity $redirectSalesChannelEntity): bool
    {
        if (null === $redirectSalesChannelEntity || null === $visibilities || null === $visibilities->getKeys()) {
            return false;
        }

        /** @var ProductVisibilityEntity $visibility */
        foreach($visibilities as $visibility) {
            if ($visibility->getSalesChannelId() === $redirectSalesChannelEntity->getId()) {
                return true;
            }
        }

        return false;
    }
}
