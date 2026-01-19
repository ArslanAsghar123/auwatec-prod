<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Sitemap;

use Doctrine\DBAL\ArrayParameterType;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\RobotsTagStruct;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Test\Core\Sitemap\CategoryFetcherTest;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Sitemap\Service\ConfigHandler;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/** @see CategoryFetcherTest */
class CategoryFetcher extends AbstractEntityFetcher
{
    public function __construct(
        private readonly ConfigHandler $configHandler,
        private readonly IteratorFactory $iteratorFactory,
        private readonly SystemConfigService $systemConfigService,
        private readonly LanguageChainFactory $languageChainFactory,
        private readonly CustomSettingLoader $customSettingLoader,
        private readonly CategoryDefinition $definition
    ) { }

    public function getNextAvailableOffset(SalesChannelContext $context, int $limit, ?int $offset): ?int
    {
        $lastId = ['offset' => $offset + $limit];
        $iterator = $this->iteratorFactory->createIterator($this->definition, $lastId);

        return $iterator->getQuery()->executeQuery()->rowCount() > 0 ? $offset + $limit : null;
    }

    /**
     * @return list<array{id: string, created_at: string, updated_at: string}>
     */
    public function getCategories(SalesChannelContext $context, int $limit, ?int $offset): array
    {
        $lastId = null;
        if ($offset) {
            $lastId = ['offset' => $offset];
        }

        $iterator = $this->iteratorFactory->createIterator($this->definition, $lastId);
        $query = $iterator->getQuery();
        $query->setMaxResults($limit);

        $query->addSelect([
            '`category`.created_at',
            '`category`.updated_at',
        ]);

        $wheres = [];
        $categoryIds = array_filter([
            $context->getSalesChannel()->getNavigationCategoryId(),
            $context->getSalesChannel()->getFooterCategoryId(),
            $context->getSalesChannel()->getServiceCategoryId(),
        ]);

        foreach ($categoryIds as $id) {
            $wheres[] = '`category`.path LIKE ' . $query->createNamedParameter('%|' . $id . '|%');
        }

        $query->andWhere('(' . implode(' OR ', $wheres) . ')');
        $query->andWhere('`category`.version_id = :versionId');
        $query->andWhere('`category`.active = 1');
        $query->andWhere('`category`.type != :linkType');
        $query->andWhere('`category`.type != :folderType');

        $excludedCategoryIds = $this->getExcludedCategoryIds($context);
        if (!empty($excludedCategoryIds)) {
            $query->andWhere('`category`.id NOT IN (:categoryIds)');
            $query->setParameter('categoryIds', Uuid::fromHexToBytesList($excludedCategoryIds), ArrayParameterType::BINARY);
        }

        $query->setParameter('versionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $query->setParameter('linkType', CategoryDefinition::TYPE_LINK);
        $query->setParameter('folderType', CategoryDefinition::TYPE_FOLDER);

        /** CORE-CHANGES - START  */
        $languageChain = $this->languageChainFactory->getLanguageIdChain($context->getLanguageId());

        if (empty($languageChain[1])) {
            // If the language has no parent language, we set the default language as parent language
            $languageChain[1] = $languageChain[2];
        }

        $query->leftJoin(
            '`category`',
            'category_translation',
            'category_translation',
            '`category`.id = category_translation.category_id AND category_translation.language_id = :languageId'
        );

        $query->leftJoin(
            '`category`',
            'category_translation',
            'category_translation_inherit_language',
            '`category`.id = category_translation_inherit_language.category_id AND category_translation_inherit_language.language_id = :inheritLanguageId'
        );

        $query->leftJoin(
            '`category`',
            'category_translation',
            'category_translation_default_language',
            '`category`.id = category_translation_default_language.category_id AND category_translation_default_language.language_id = :defaultLanguageId'
        );

        $query->setParameter('languageId', Uuid::fromHexToBytes($languageChain[0]));
        $query->setParameter('inheritLanguageId', Uuid::fromHexToBytes($languageChain[1]));
        $query->setParameter('defaultLanguageId', Uuid::fromHexToBytes($languageChain[2]));

        $query->addSelect([
            '`category_translation`.custom_fields as customFields_currentLanguage',
            '`category_translation_inherit_language`.custom_fields as customFields_inheritLanguage',
            '`category_translation_default_language`.custom_fields as customFields_defaultLanguage',
        ]);
        /** CORE-CHANGES - END  */

        /** @var list<array{id: string, created_at: string, updated_at: string}> $result */
        $result = $query->executeQuery()->fetchAllAssociative();

        /** CORE-CHANGES - START  */
        $result = array_map(static function (array $item) {
            /** Decode the custom fields */
            if (!empty($item['customFields_currentLanguage'])) {
                $item['customFields_currentLanguage'] = json_decode($item['customFields_currentLanguage'], true);
            }

            if (!empty($item['customFields_inheritLanguage'])) {
                $item['customFields_inheritLanguage'] = json_decode($item['customFields_inheritLanguage'], true);
            }

            if (!empty($item['customFields_defaultLanguage'])) {
                $item['customFields_defaultLanguage'] = json_decode($item['customFields_defaultLanguage'], true);
            }

            if (isset($item['customFields_currentLanguage']['dreisc_seo_sitemap_inactive']) && null !== $item['customFields_currentLanguage']['dreisc_seo_sitemap_inactive']) {
                $item['sitemapInactive'] = $item['customFields_currentLanguage']['dreisc_seo_sitemap_inactive'];
            } elseif (isset($item['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive']) && null !== $item['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive']) {
                $item['sitemapInactive'] = $item['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive'];
            } elseif (isset($item['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive']) && null !== $item['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive']) {
                $item['sitemapInactive'] = $item['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive'];
            } else {
                $item['sitemapInactive'] = false;
            }

            if (isset($item['customFields_currentLanguage']['dreisc_seo_sitemap_priority']) && !empty($item['customFields_currentLanguage']['dreisc_seo_sitemap_priority'])) {
                $item['sitemapPriority'] = $item['customFields_currentLanguage']['dreisc_seo_sitemap_priority'];
            } elseif (isset($item['customFields_inheritLanguage']['dreisc_seo_sitemap_priority']) && !empty($item['customFields_inheritLanguage']['dreisc_seo_sitemap_priority'])) {
                $item['sitemapPriority'] = $item['customFields_inheritLanguage']['dreisc_seo_sitemap_priority'];
            } elseif (isset($item['customFields_defaultLanguage']['dreisc_seo_sitemap_priority']) && !empty($item['customFields_defaultLanguage']['dreisc_seo_sitemap_priority'])) {
                $item['sitemapPriority'] = $item['customFields_defaultLanguage']['dreisc_seo_sitemap_priority'];
            } else {
                $item['sitemapPriority'] = 0.5;
            }

            return $item;
        }, $result);

        $customSetting = $this->customSettingLoader->load($context->getSalesChannelId(), true);
        $hideInSitemapIfSeoUrlNotEqualCanonical = $customSetting->getSitemap()->getGeneral()->getHideInSitemapIfSeoUrlNotEqualCanonical();
        $hideInSitemapIfRobotsTagNoindex = $customSetting->getSitemap()->getGeneral()->getHideInSitemapIfRobotsTagNoindex();

        foreach($result as &$item) {
            $item['dreisc_seo_canonical_link_type']  = $this->getTranslated($item, 'dreisc_seo_canonical_link_type');
            $item['dreisc_seo_canonical_link_reference']  = $this->getTranslated($item, 'dreisc_seo_canonical_link_reference');

            if ($hideInSitemapIfSeoUrlNotEqualCanonical && null !== $item['dreisc_seo_canonical_link_type'] && null !== $item['dreisc_seo_canonical_link_reference']) {
                $item['dreisc_seo_canonical_link_type'] = $this->groupBySalesChannel($item['dreisc_seo_canonical_link_type']);
                $item['dreisc_seo_canonical_link_reference'] = $this->groupBySalesChannel($item['dreisc_seo_canonical_link_reference']);

                if (empty($item['dreisc_seo_canonical_link_type'][$context->getSalesChannelId()]) || empty($item['dreisc_seo_canonical_link_reference'][$context->getSalesChannelId()])) {
                    continue;
                }

                if (
                    !empty($item['dreisc_seo_canonical_link_type'][$context->getSalesChannelId()]['type']) &&
                    ProductEnum::CANONICAL_LINK_TYPE__CATEGORY_URL === $item['dreisc_seo_canonical_link_type'][$context->getSalesChannelId()]['type'] &&
                    !empty($item['dreisc_seo_canonical_link_reference'][$context->getSalesChannelId()]['reference']) &&
                    $item['id'] === $item['dreisc_seo_canonical_link_reference'][$context->getSalesChannelId()]['reference']
                ) {
                    /** Link to itself */
                    continue;
                }

                if (!in_array($item['dreisc_seo_canonical_link_type'][$context->getSalesChannelId()]['type'], ProductEnum::VALID_CANONICAL_LINK_TYPES, true)) {
                    /** Invalid canonical link type */
                    continue;
                }

                $item['sitemapInactive'] = true;
            }
        }

        foreach($result as &$item) {
            $robotsTag = $this->getTranslated($item, 'dreisc_seo_robots_tag');
            if ($hideInSitemapIfRobotsTagNoindex) {
                if (!in_array($robotsTag, RobotsTagStruct::VALID_ROBOTS_TAGS, true)) {
                    $robotsTag = $customSetting->getMetaTags()->getRobotsTag()->getDefaultRobotsTagProduct();
                }

                if (!empty($robotsTag) && in_array($robotsTag, [RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW, RobotsTagStruct::ROBOTS_TAG__NOINDEX_NOFOLLOW], true)) {
                    $item['sitemapInactive'] = true;
                }
            }
        }

        $result = array_values(array_filter($result, static function (array $category) {
            return !$category['sitemapInactive'];
        }));

        return $result;
    }

    /**
     * @return array<string>
     */
    private function getExcludedCategoryIds(SalesChannelContext $salesChannelContext): array
    {
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();

        $excludedUrls = $this->configHandler->get(ConfigHandler::EXCLUDED_URLS_KEY);
        if (empty($excludedUrls)) {
            return [];
        }

        $excludedUrls = array_filter($excludedUrls, static function (array $excludedUrl) use ($salesChannelId) {
            if ($excludedUrl['resource'] !== CategoryEntity::class) {
                return false;
            }

            if ($excludedUrl['salesChannelId'] !== $salesChannelId) {
                return false;
            }

            return true;
        });

        return array_column($excludedUrls, 'identifier');
    }
}
