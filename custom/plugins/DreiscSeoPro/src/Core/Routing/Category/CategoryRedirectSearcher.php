<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Routing\Category;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Core\Routing\RedirectExecutor;
use DreiscSeoPro\Core\Routing\RedirectFinder;
use DreiscSeoPro\Core\Routing\SourceSalesChannelDomainRestrictionChecker;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\HttpFoundation\Request;

class CategoryRedirectSearcher
{
    public function __construct(
        private readonly DreiscSeoRedirectRepository $dreiscSeoRedirectRepository,
        private readonly RedirectExecutor $redirectExecutor,
        private readonly SourceSalesChannelDomainRestrictionChecker $sourceSalesChannelDomainRestrictionChecker
    ) { }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function search(Request $request, string $categoryId): void
    {
        /** Check, if there is a redirect for this product */
        $dreiscSeoRedirectEntity = $this->dreiscSeoRedirectRepository->getSourceTypeCategoryByCategoryId($categoryId);
        if(null === $dreiscSeoRedirectEntity) {
            return;
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
