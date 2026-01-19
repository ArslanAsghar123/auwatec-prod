<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\SeoUrl;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlDefinition;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlEntity;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

/**
* @method SeoUrlEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method SeoUrlSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class SeoUrlRepository extends EntityRepository
{
    final const ROUTE_NAME__FRONTEND_NAVIGATION_PAGE = 'frontend.navigation.page';
    final const ROUTE_NAME__FRONTEND_DETAIL_PAGE = 'frontend.detail.page';

    final const SEO_FILTER__IS_CANONICAL = 'isCanonical';
    final const SEO_FILTER__IS_MODIFIED = 'isModified';
    final const SEO_FILTER__IS_DELETED = 'isDeleted';

    final const PATH_INFO_PREFIXES = [
        self::ROUTE_NAME__FRONTEND_NAVIGATION_PAGE => '/navigation/',
        self::ROUTE_NAME__FRONTEND_DETAIL_PAGE => '/detail/',
    ];

    /**
     * @param Connection $connection
     */
    public function __construct(\Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository, private readonly Connection $connection, private readonly EntityCacheKeyGenerator $entityCacheKeyGenerator, private readonly CacheClearer $cacheClearer)
    {
        parent::__construct($repository);
    }

    /**
     * @param array|null $seoFilters
     * @throws InconsistentCriteriaIdsException
     */
    public function getByContext(string $languageId, ?string $salesChannelId, string $routeName, string $foreignKey, array $seoFilters = []): EntitySearchResult
    {
        $criteria = (new Criteria())
            ->addFilter(
                new EqualsFilter('languageId', $languageId),
                new EqualsFilter('salesChannelId', $salesChannelId),
                new EqualsFilter('routeName', $routeName),
                new EqualsFilter('foreignKey', $foreignKey)
            );

        /** Set the filters */
        foreach($seoFilters as $seoFilter => $seoFilterValue) {
            if (!is_bool($seoFilterValue) && null !== $seoFilterValue) {
                throw new \RuntimeException('$seoFilterValue is not boolean and not NULL');
            }

            $criteria->addFilter(
                new EqualsFilter($seoFilter, $seoFilterValue)
            );
        }

        return $this->search($criteria, Context::createDefaultContext());
    }

    /**
     * @return SeoUrlEntity|null
     * @throws InconsistentCriteriaIdsException
     */
    public function getBySeoPathInfo(string $languageId, ?string $salesChannelId, string $seoPathInfo)
    {
        $criteria = (new Criteria())
            ->addFilter(
                new EqualsFilter('languageId', $languageId),
                new EqualsFilter('salesChannelId', $salesChannelId),
                new EqualsFilter('seoPathInfo', $seoPathInfo)
            );

        $result = $this->search($criteria, Context::createDefaultContext());

        return $result->first();
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function resetCanonicalFlag(string $languageId, ?string $salesChannelId, string $foreignKey, string $routeName): void
    {
        /** Search the relevant ids */
        $ids = $this->searchIds(
            (new Criteria())
                ->addFilter(
                    new EqualsFilter('languageId', $languageId),
                    new EqualsFilter('salesChannelId', $salesChannelId),
                    new EqualsFilter('foreignKey', $foreignKey),
                    new EqualsFilter('routeName', $routeName)
                ),
            Context::createDefaultContext()
        );

        /** Abort, if no item available */
        if (0 === $ids->getTotal()) {
            return;
        }

        /** Update the canonical flag */
        $this->connection->createQueryBuilder()
            ->update(SeoUrlDefinition::ENTITY_NAME)
            ->set('is_canonical', ':isCanonical')
            ->setParameter('isCanonical', null)
            ->where('id IN (:ids)')
            ->setParameter('ids', Uuid::fromHexToBytesList($ids->getIds()), Connection::PARAM_STR_ARRAY)
            ->execute();
    }
}

