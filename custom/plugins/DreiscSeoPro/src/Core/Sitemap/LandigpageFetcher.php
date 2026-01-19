<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Sitemap;

use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\RobotsTagStruct;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Test\Core\Sitemap\LandigpageFetcherTest;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/** @see LandigpageFetcherTest */
class LandigpageFetcher extends AbstractEntityFetcher
{
    public function __construct(
        private readonly LanguageChainFactory $languageChainFactory,
        private readonly IteratorFactory $iteratorFactory,
        private readonly LandingPageDefinition $landingPageDefinition,
        private readonly CustomSettingLoader $customSettingLoader
    ) { }

    public function getNextAvailableOffset(SalesChannelContext $context, int $limit, ?int $offset): ?int
    {
        $lastId = ['offset' => $offset + $limit];
        $iterator = $this->iteratorFactory->createIterator($this->landingPageDefinition, $lastId);

        return $iterator->getQuery()->executeQuery()->rowCount() > 0 ? $offset + $limit : null;
    }
    public function getLandingpages(SalesChannelContext $context, int $limit, ?int $offset): array
    {
        $lastId = null;
        if ($offset) {
            $lastId = ['offset' => $offset];
        }

        $iterator = $this->iteratorFactory->createIterator($this->landingPageDefinition, $lastId);
        $query = $iterator->getQuery();
        $query->setMaxResults($limit);

        $query->addSelect([
            '`landing_page`.created_at',
            '`landing_page`.updated_at',
            '`landing_page`.id',
        ]);

        $query->innerJoin(
            '`landing_page`',
            'landing_page_sales_channel',
            'landing_page_sales_channel',
            '`landing_page`.id = landing_page_sales_channel.landing_page_id AND landing_page_sales_channel.sales_channel_id = :salesChannelId'
        );

        $query->andWhere('`landing_page`.active = 1');
        $query->andWhere('`landing_page`.version_id = :versionId');

        $query->setParameter('versionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $query->setParameter('salesChannelId', Uuid::fromHexToBytes($context->getSalesChannelId()));

        $languageChain = $this->languageChainFactory->getLanguageIdChain($context->getLanguageId());

        if (empty($languageChain[1])) {
            // If the language has no parent language, we set the default language as parent language
            $languageChain[1] = $languageChain[2];
        }

        $query->leftJoin(
            '`landing_page`',
            'landing_page_translation',
            'landing_page_translation',
            '`landing_page`.id = landing_page_translation.landing_page_id AND landing_page_translation.language_id = :languageId'
        );

        $query->leftJoin(
            '`landing_page`',
            'landing_page_translation',
            'landing_page_translation_inherit_language',
            '`landing_page`.id = landing_page_translation_inherit_language.landing_page_id AND landing_page_translation_inherit_language.language_id = :inheritLanguageId'
        );

        $query->leftJoin(
            '`landing_page`',
            'landing_page_translation',
            'landing_page_translation_default_language',
            '`landing_page`.id = landing_page_translation_default_language.landing_page_id AND landing_page_translation_default_language.language_id = :defaultLanguageId'
        );

        $query->setParameter('languageId', Uuid::fromHexToBytes($languageChain[0]));
        $query->setParameter('inheritLanguageId', Uuid::fromHexToBytes($languageChain[1]));
        $query->setParameter('defaultLanguageId', Uuid::fromHexToBytes($languageChain[2]));

        $query->addSelect([
            '`landing_page_translation`.custom_fields as customFields_currentLanguage',
            '`landing_page_translation_inherit_language`.custom_fields as customFields_inheritLanguage',
            '`landing_page_translation_default_language`.custom_fields as customFields_defaultLanguage',
        ]);

        /** @var list<array{id: string, created_at: string, updated_at: string}> $result */
        $result = $query->executeQuery()->fetchAllAssociative();

        $result = array_map(static function (array $result) {
            /** Decode the custom fields */
            if (!empty($result['customFields_currentLanguage'])) {
                $result['customFields_currentLanguage'] = json_decode($result['customFields_currentLanguage'], true);
            }

            if (!empty($result['customFields_inheritLanguage'])) {
                $result['customFields_inheritLanguage'] = json_decode($result['customFields_inheritLanguage'], true);
            }

            if (!empty($result['customFields_defaultLanguage'])) {
                $result['customFields_defaultLanguage'] = json_decode($result['customFields_defaultLanguage'], true);
            }

            if (isset($result['customFields_currentLanguage']['dreisc_seo_sitemap_inactive']) && null !== $result['customFields_currentLanguage']['dreisc_seo_sitemap_inactive']) {
                $result['sitemapInactive'] = $result['customFields_currentLanguage']['dreisc_seo_sitemap_inactive'];
            } elseif (isset($result['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive']) && null !== $result['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive']) {
                $result['sitemapInactive'] = $result['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive'];
            } elseif (isset($result['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive']) && null !== $result['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive']) {
                $result['sitemapInactive'] = $result['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive'];
            } else {
                $result['sitemapInactive'] = false;
            }

            if (isset($result['customFields_currentLanguage']['dreisc_seo_sitemap_priority']) && !empty($result['customFields_currentLanguage']['dreisc_seo_sitemap_priority'])) {
                $result['sitemapPriority'] = $result['customFields_currentLanguage']['dreisc_seo_sitemap_priority'];
            } elseif (isset($result['customFields_inheritLanguage']['dreisc_seo_sitemap_priority']) && !empty($result['customFields_inheritLanguage']['dreisc_seo_sitemap_priority'])) {
                $result['sitemapPriority'] = $result['customFields_inheritLanguage']['dreisc_seo_sitemap_priority'];
            } elseif (isset($result['customFields_defaultLanguage']['dreisc_seo_sitemap_priority']) && !empty($result['customFields_defaultLanguage']['dreisc_seo_sitemap_priority'])) {
                $result['sitemapPriority'] = $result['customFields_defaultLanguage']['dreisc_seo_sitemap_priority'];
            } else {
                $result['sitemapPriority'] = 0.5;
            }

            $result['id'] = Uuid::fromBytesToHex($result['id']);

            return $result;
        }, $result);

        $customSetting = $this->customSettingLoader->load($context->getSalesChannelId(), true);
        $hideInSitemapIfSeoUrlNotEqualCanonical = $customSetting->getSitemap()->getGeneral()->getHideInSitemapIfSeoUrlNotEqualCanonical();
        $hideInSitemapIfRobotsTagNoindex = $customSetting->getSitemap()->getGeneral()->getHideInSitemapIfRobotsTagNoindex();

        foreach($result as &$item) {
            $item['dreisc_seo_canonical_link_type']  = $this->getTranslated($item, 'dreisc_seo_canonical_link_type');
            $item['dreisc_seo_canonical_link_reference']  = $this->getTranslated($item, 'dreisc_seo_canonical_link_reference');

            if ($hideInSitemapIfSeoUrlNotEqualCanonical && !empty($item['dreisc_seo_canonical_link_type']) && !empty($item['dreisc_seo_canonical_link_reference'])) {
                if (!in_array($item['dreisc_seo_canonical_link_type'], ProductEnum::VALID_CANONICAL_LINK_TYPES, true)) {
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
}
