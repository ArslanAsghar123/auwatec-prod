<?php declare(strict_types=1);

namespace Atl\SeoUrlManager\Storefront\Framework\Seo\SeoUrlRoute;

use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\ProductPageSeoUrlRoute;

class ProductPageSeoUrlRouteDecorator implements SeoUrlRouteInterface
{
    /**
     * @var ProductPageSeoUrlRoute
     */
    private $decoratedService;

    public function __construct(SeoUrlRouteInterface $seoUrlRoute)
    {
        $this->decoratedService = $seoUrlRoute;
    }

    public function getDecorated(): SeoUrlRouteInterface
    {
        return $this->decoratedService;
    }

    public function getConfig(): SeoUrlRouteConfig
    {
        return $this->decoratedService->getConfig();
    }

    public function prepareCriteria(Criteria $criteria, SalesChannelEntity $salesChannel): void
    {
        $this->decoratedService->prepareCriteria($criteria, $salesChannel);
        $criteria->addAssociation('options');
        $criteria->addAssociation('options.group');
    }

    public function getMapping(Entity $entity, ?SalesChannelEntity $salesChannel): SeoUrlMapping
    {
        return $this->decoratedService->getMapping($entity, $salesChannel);
    }
}
