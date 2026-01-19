<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Seo;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use RuntimeException;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class SeoUrlAssembler
{
    final public const IS_SEO_URL = 'isSeoUrl';
    final public const TECHNICAL_PATH_INFO = 'technicalPathInfo';
    final public const PATH_INFO = 'pathInfo';
    final public const ABSOLUTE_PATHS = 'absolutePaths';

    /**
     * @var EntityRepository
     */
    private $seoUrlRepository;

    /**
     * @var EntityRepository
     */
    private $salesChannelDomainRepository;

    public function __construct(EntityRepository $seoUrlRepository, EntityRepository $salesChannelDomainRepository)
    {
        $this->seoUrlRepository = $seoUrlRepository;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
    }

    /**
     * Determined the url info of the given entity
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function assemble(Entity $entity, string $salesChannelId, string $languageId): array
    {
        $pathInfo = match (true) {
            $entity instanceof ProductEntity => $this->getProductPathInfo($entity),
            $entity instanceof CategoryEntity => $this->getCategoryPathInfo($entity),
            default => throw new RuntimeException('Unknown entity: ' . $entity::class),
        };

        /** Add the technical url to the result array */
        $urlInfo[self::TECHNICAL_PATH_INFO] = $pathInfo;

        /** Set fallback values */
        $urlInfo[self::PATH_INFO] = $urlInfo[self::TECHNICAL_PATH_INFO];
        $urlInfo[self::IS_SEO_URL] = false;

        /** Try to find the seo url */
        /** @var SeoUrlEntity $seoUrlEntity */
        $seoUrlEntity = $this->seoUrlRepository->search(
            (new Criteria())
                ->setLimit(1)
                ->addFilter(
                    new MultiFilter(
                        MultiFilter::CONNECTION_AND,
                        [
                            new EqualsFilter('salesChannelId', $salesChannelId),
                            new EqualsFilter('languageId', $languageId),
                            new EqualsFilter('pathInfo', $urlInfo['technicalPathInfo']),
                            new EqualsFilter('isDeleted', false),
                            new EqualsFilter('isCanonical', true)
                        ]
                    )
                ),
            Context::createDefaultContext()
        )->first();

        if(null === $seoUrlEntity) {
            return $this->fetchAbsolutePaths($urlInfo, $salesChannelId, $languageId);
        }

        /** Add additional values */
        $urlInfo[self::IS_SEO_URL] = true;
        $urlInfo[self::PATH_INFO] = $seoUrlEntity->getSeoPathInfo();

        return $this->fetchAbsolutePaths($urlInfo, $salesChannelId, $languageId);
    }

    protected function getProductPathInfo(ProductEntity $productEntity): string
    {
        return sprintf(
            '/detail/%s',
            $productEntity->getId()
        );
    }

    protected function getCategoryPathInfo(CategoryEntity $categoryEntity): string
    {
        return sprintf(
            '/navigation/%s',
            $categoryEntity->getId()
        );
    }

    protected function fetchAbsolutePaths(array $urlInfo, string $salesChannelId, string $languageId)
    {
        $absolutePaths = [];

        /** Fetch all sales channel domains for the given sales channel and language */
        $searchResult = $this->salesChannelDomainRepository->search(
            (new Criteria())
                ->addFilter(
                    new MultiFilter(
                        MultiFilter::CONNECTION_AND,
                        [
                            new EqualsFilter('salesChannelId', $salesChannelId),
                            new EqualsFilter('languageId', $languageId)
                        ]
                    )
                ),
            Context::createDefaultContext()
        );

        /** @var SalesChannelDomainEntity $salesChannelDomain */
        foreach($searchResult->getEntities() as $salesChannelDomain) {
            $url = rtrim($salesChannelDomain->getUrl(), '/');
            $pathInfo = ltrim((string) $urlInfo[self::PATH_INFO], '/');

            $absolutePaths[$salesChannelDomain->getId()] = sprintf(
                '%s/%s',
                $url,
                $pathInfo
            );
        }

        $urlInfo[self::ABSOLUTE_PATHS] = $absolutePaths;

        return $urlInfo;
    }
}
