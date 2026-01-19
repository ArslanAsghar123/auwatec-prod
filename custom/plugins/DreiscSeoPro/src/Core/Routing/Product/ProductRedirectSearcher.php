<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Routing\Product;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Routing\RedirectExecutor;
use DreiscSeoPro\Core\Routing\RedirectFinder;
use DreiscSeoPro\Core\Routing\SourceSalesChannelDomainRestrictionChecker;
use DreiscSeoPro\Test\Core\Routing\Product\ProductRedirectSearcherTest;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\HttpFoundation\Request;

/** @see ProductRedirectSearcherTest */
class ProductRedirectSearcher
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(
        private readonly DreiscSeoRedirectRepository $dreiscSeoRedirectRepository,
        private readonly RedirectExecutor $redirectExecutor,
        private readonly SourceSalesChannelDomainRestrictionChecker  $sourceSalesChannelDomainRestrictionChecker,
        ProductRepository $productRepository
    )
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function search(Request $request, string $productId): void
    {
        /** Check, if there is a redirect for this product */
        $dreiscSeoRedirectEntity = $this->dreiscSeoRedirectRepository->getSourceTypeProductByProductId($productId);

        if(null === $dreiscSeoRedirectEntity) {
            /** In case of variant product, we check for the parent product id */
            $productEntity = $this->productRepository->get($productId);
            if (null !== $productEntity && null !== $productEntity->getParentId()) {
                $dreiscSeoRedirectEntity = $this->dreiscSeoRedirectRepository->getSourceTypeProductByProductId($productEntity->getParentId());
            }

            if (null === $dreiscSeoRedirectEntity) {
                return;
            }
        }

        /** Check for a sales channel domain restriction */
        if(false === $this->sourceSalesChannelDomainRestrictionChecker->isValidRedirect(
            $dreiscSeoRedirectEntity,
            $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID)
        )) {
            return;
        }

        $queryString= $request->getQueryString();
        if(!empty($queryString)) {
            parse_str($queryString, $params);

            if (is_array($params)) {
                $dreiscSeoRedirectEntity->addExtension(
                    RedirectFinder::QUERY_PARAMS,
                    new ArrayStruct($params)
                );
            }
        }

        /** Execute the redirect */
        $this->redirectExecutor->redirect(
            $dreiscSeoRedirectEntity,
            $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_ID)
        );
    }
}
