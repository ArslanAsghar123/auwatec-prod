<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect;

use Composer\Cache;
use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\PrefixFilter;
use Shopware\Core\Framework\Uuid\Uuid;

/**
* @method DreiscSeoRedirectSearchResult    search(Criteria $criteria, Context $context = null)
*/
class DreiscSeoRedirectRepository extends EntityRepository
{
    /**
     * @param \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository
     * @param Connection $connection
     * @param CacheClearer $cacheClearer
     * @param EntityCacheKeyGenerator $entityCacheKeyGenerator
     */
    public function __construct(\Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository, private readonly Connection $connection, private readonly CacheClearer $cacheClearer, private readonly EntityCacheKeyGenerator $entityCacheKeyGenerator)
    {
        parent::__construct($repository);
    }

    /**
     * Fetch a sourceType=url redirect by the domain id and the path
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getSourceTypeUrlByDomainIdAndSourcePath(string $sourceSalesChannelDomainId, string $sourcePath, bool $sourcePathStartsWith = false): ?DreiscSeoRedirectCollection
    {
        $sourcePath = urldecode($sourcePath);

        if ($sourcePathStartsWith) {
            if(empty($sourcePath)) {
                $sourcePathFilter = new PrefixFilter(
                    DreiscSeoRedirectEntity::SOURCE_PATH__PROPERTY_NAME,
                    '?'
                );
            } else {
                $sourcePathFilter = new OrFilter([
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::SOURCE_PATH__PROPERTY_NAME,
                        $sourcePath
                    ),
                    new PrefixFilter(
                        DreiscSeoRedirectEntity::SOURCE_PATH__PROPERTY_NAME,
                        $sourcePath . '?'
                    )
                ]);
            }
        } else {
            $sourcePathFilter = new EqualsFilter(
                DreiscSeoRedirectEntity::SOURCE_PATH__PROPERTY_NAME,
                $sourcePath
            );
        }

        $searchResult = $this->search(
            (new Criteria())->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_AND, [
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::ACTIVE__PROPERTY_NAME,
                        true
                    ),
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::SOURCE_TYPE__PROPERTY_NAME,
                        DreiscSeoRedirectEnum::SOURCE_TYPE__URL
                    ),
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::SOURCE_SALES_CHANNEL_DOMAIN_ID__PROPERTY_NAME,
                        $sourceSalesChannelDomainId
                    ),
                    $sourcePathFilter
                ])
            )
        );

        /** Return the found entity */
        return $searchResult->getEntities();
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function getSourceTypeProductByProductId(string $productId): ?DreiscSeoRedirectEntity
    {
        $searchResult = $this->search(
            (new Criteria())->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_AND, [
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::ACTIVE__PROPERTY_NAME,
                        true
                    ),
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::SOURCE_TYPE__PROPERTY_NAME,
                        DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT
                    ),
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::SOURCE_PRODUCT_ID__PROPERTY_NAME,
                        $productId
                    )
                ])
            )
        );

        /** Return the found entity */
        return $searchResult->first();
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function getSourceTypeCategoryByCategoryId(string $categoryId): ?DreiscSeoRedirectEntity
    {
        $searchResult = $this->search(
            (new Criteria())->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_AND, [
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::ACTIVE__PROPERTY_NAME,
                        true
                    ),
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::SOURCE_TYPE__PROPERTY_NAME,
                        DreiscSeoRedirectEnum::SOURCE_TYPE__CATEGORY
                    ),
                    new EqualsFilter(
                        DreiscSeoRedirectEntity::SOURCE_CATEGORY_ID__PROPERTY_NAME,
                        $categoryId
                    )
                ])
            )
        );

        /** Return the found entity */
        return $searchResult->first();
    }

    /**
     * This is a workaround because the entity is not deletable by the shopware DAL
     * @see: https://issues.shopware.com/issues/NEXT-7866
     *
     * @param $dreiscSeoRedirectId
     * @throws \Doctrine\DBAL\DBALException
     */
    public function plainDeleteById($dreiscSeoRedirectId)
    {
        $this->connection->executeStatement(
            'DELETE FROM `dreisc_seo_redirect` WHERE `id` = :dreiscSeoRedirectId',
            ['dreiscSeoRedirectId' => Uuid::fromHexToBytes($dreiscSeoRedirectId)]
        );
    }
}

