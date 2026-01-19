<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Sitemap;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\RobotsTagStruct;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Decorator\Core\Content\Sitemap\Provider\ProductUrlProvider;
use DreiscSeoPro\Test\Core\Sitemap\ProductFetcherTest;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Sitemap\Service\ConfigHandler;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/** @see ProductFetcherTest */
class ProductFetcher
{
    public function __construct(
        private readonly ConfigHandler $configHandler,
        private readonly Connection $connection,
        private readonly ProductDefinition $productDefinition,
        private readonly IteratorFactory $iteratorFactory,
        private readonly SystemConfigService $systemConfigService,
        private readonly LanguageChainFactory $languageChainFactory,
        private readonly CustomSettingLoader $customSettingLoader
    ) { }

    public function getNextAvailableOffset(SalesChannelContext $context, int $limit, ?int $offset): ?int
    {
        $lastId = ['offset' => $offset + $limit];
        $iterator = $this->iteratorFactory->createIterator($this->productDefinition, $lastId);

        return $iterator->getQuery()->executeQuery()->rowCount() > 0 ? $offset + $limit : null;
    }

    /**
     * @see \Shopware\Core\Content\Sitemap\Provider\ProductUrlProvider::getProducts()
     * @return list<array{id: string, created_at: string, updated_at: string}>
     */
    public function getProducts(SalesChannelContext $context, int $limit, ?int $offset): array
    {
        $lastId = null;
        if ($offset) {
            $lastId = ['offset' => $offset];
        }

        $iterator = $this->iteratorFactory->createIterator($this->productDefinition, $lastId);
        $query = $iterator->getQuery();
        $query->setMaxResults($limit);

        $showAfterCloseout = !$this->systemConfigService->get(ProductUrlProvider::CONFIG_HIDE_AFTER_CLOSEOUT, $context->getSalesChannelId());
        $customSetting = $this->customSettingLoader->load($context->getSalesChannelId(), true);

        $parentCanonicalInheritance = $customSetting->getCanonical()->getGeneral()->getParentCanonicalInheritance();
        $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled = $customSetting->getSitemap()->getGeneral()->getParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled();
        $hideInSitemapIfSeoUrlNotEqualCanonical = $customSetting->getSitemap()->getGeneral()->getHideInSitemapIfSeoUrlNotEqualCanonical();
        $hideInSitemapIfRobotsTagNoindex = $customSetting->getSitemap()->getGeneral()->getHideInSitemapIfRobotsTagNoindex();

        $query->addSelect([
            '`product`.id as product_id',
            '`product`.parent_id as parent_id',
            '`product`.child_count as child_count',
            '`product`.created_at as created_at',
            '`product`.updated_at as updated_at',
            '`product`.product_number as product_number',
        ]);

        $query->leftJoin('`product`', '`product`', 'parent', '`product`.parent_id = parent.id');
        $query->innerJoin('`product`', 'product_visibility', 'visibilities', 'product.visibilities = visibilities.product_id');

        $query->andWhere('`product`.version_id = :versionId');

        if ($showAfterCloseout) {
            $query->andWhere('(`product`.available = 1 OR `product`.is_closeout)');
        } else {
            $query->andWhere('`product`.available = 1');
        }

        $query->andWhere('IFNULL(`product`.active, parent.active) = 1');


        //$query->andWhere('(`product`.child_count = 0 OR `product`.parent_id IS NOT NULL)');

        $query->andWhere('(`product`.parent_id IS NULL OR parent.canonical_product_id IS NULL OR parent.canonical_product_id = `product`.id)');
        $query->andWhere('visibilities.product_version_id = :versionId');
        $query->andWhere('visibilities.sales_channel_id = :salesChannelId');

        $excludedProductIds = $this->getExcludedProductIds($context);
        if (!empty($excludedProductIds)) {
            $query->andWhere('`product`.id NOT IN (:productIds)');
            $query->setParameter('productIds', Uuid::fromHexToBytesList($excludedProductIds), ArrayParameterType::BINARY);
        }

        $query->setParameter('versionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $query->setParameter('salesChannelId', Uuid::fromHexToBytes($context->getSalesChannelId()));

        /** CORE-CHANGES - START  */
        $languageChain = $this->languageChainFactory->getLanguageIdChain($context->getLanguageId());

        if (empty($languageChain[1])) {
            // If the language has no parent language, we set the default language as parent language
            $languageChain[1] = $languageChain[2];
        }

        /** Load the translations */
        $query->leftJoin(
            '`product`',
            'product_translation',
            'product_translation',
            '`product`.id = product_translation.product_id AND product_translation.language_id = :languageId'
        );

        $query->leftJoin(
            '`product`',
            'product_translation',
            'product_translation_inherit_language',
            '`product`.id = product_translation_inherit_language.product_id AND product_translation_inherit_language.language_id = :inheritLanguageId'
        );

        $query->leftJoin(
            '`product`',
            'product_translation',
            'product_translation_default_language',
            '`product`.id = product_translation_default_language.product_id AND product_translation_default_language.language_id = :defaultLanguageId'
        );

        /** Load the translations of the parent product */
        $query->leftJoin(
            '`product`',
            'product_translation',
            'parent_product_translation',
            '`product`.parent_id = parent_product_translation.product_id AND parent_product_translation.language_id = :languageId'
        );

        $query->leftJoin(
            '`product`',
            'product_translation',
            'parent_product_translation_inherit_language',
            '`product`.parent_id = parent_product_translation_inherit_language.product_id AND parent_product_translation_inherit_language.language_id = :inheritLanguageId'
        );

        $query->leftJoin(
            '`product`',
            'product_translation',
            'parent_product_translation_default_language',
            '`product`.parent_id = parent_product_translation_default_language.product_id AND parent_product_translation_default_language.language_id = :defaultLanguageId'
        );

        $query->setParameter('languageId', Uuid::fromHexToBytes($languageChain[0]));
        $query->setParameter('inheritLanguageId', Uuid::fromHexToBytes($languageChain[1]));
        $query->setParameter('defaultLanguageId', Uuid::fromHexToBytes($languageChain[2]));

        $query->addSelect([
            '`product_translation`.custom_fields as customFields_currentLanguage',
            '`product_translation_inherit_language`.custom_fields as customFields_inheritLanguage',
            '`product_translation_default_language`.custom_fields as customFields_defaultLanguage',

            '`parent_product_translation`.custom_fields as parent_customFields_currentLanguage',
            '`parent_product_translation_inherit_language`.custom_fields as parent_customFields_inheritLanguage',
            '`parent_product_translation_default_language`.custom_fields as parent_customFields_defaultLanguage',
        ]);
        /** CORE-CHANGES - END  */

        /** @var list<array{id: string, created_at: string, updated_at: string}> $result */
        $result = $query->executeQuery()->fetchAllAssociative();

        foreach($result as &$item) {
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

            if (!empty($item['parent_customFields_currentLanguage'])) {
                $item['parent_customFields_currentLanguage'] = json_decode($item['parent_customFields_currentLanguage'], true);
            }

            if (!empty($item['parent_customFields_inheritLanguage'])) {
                $item['parent_customFields_inheritLanguage'] = json_decode($item['parent_customFields_inheritLanguage'], true);
            }

            if (!empty($item['parent_customFields_defaultLanguage'])) {
                $item['parent_customFields_defaultLanguage'] = json_decode($item['parent_customFields_defaultLanguage'], true);
            }

            if (isset($item['customFields_currentLanguage']['dreisc_seo_sitemap_inactive']) && is_bool($item['customFields_currentLanguage']['dreisc_seo_sitemap_inactive'])) {
                $item['sitemapInactive'] = $item['customFields_currentLanguage']['dreisc_seo_sitemap_inactive'];
            } elseif (isset($item['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive']) && is_bool($item['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive'])) {
                $item['sitemapInactive'] = $item['customFields_inheritLanguage']['dreisc_seo_sitemap_inactive'];
            } elseif (isset($item['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive']) && is_bool($item['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive'])) {
                $item['sitemapInactive'] = $item['customFields_defaultLanguage']['dreisc_seo_sitemap_inactive'];
            } else {
                $item['sitemapInactive'] = false;
            }

            if (!$parentCanonicalInheritance) {
                if(empty($item['parent_id'])) {
                    /** Main product */
                    if (isset($item['customFields_currentLanguage']['enable_parent_canonical_inheritance']) && is_bool($item['customFields_currentLanguage']['enable_parent_canonical_inheritance'])) {
                        $parentCanonicalInheritance = $item['customFields_currentLanguage']['enable_parent_canonical_inheritance'];
                    } elseif (isset($item['customFields_inheritLanguage']['enable_parent_canonical_inheritance']) && is_bool($item['customFields_inheritLanguage']['enable_parent_canonical_inheritance'])) {
                        $parentCanonicalInheritance = $item['customFields_inheritLanguage']['enable_parent_canonical_inheritance'];
                    } elseif (isset($item['customFields_defaultLanguage']['enable_parent_canonical_inheritance']) && is_bool($item['customFields_defaultLanguage']['enable_parent_canonical_inheritance'])) {
                        $parentCanonicalInheritance = $item['customFields_defaultLanguage']['enable_parent_canonical_inheritance'];
                    }
                } else {
                    /** Variant product */
                    if (isset($item['parent_customFields_currentLanguage']['enable_parent_canonical_inheritance']) && is_bool($item['parent_customFields_currentLanguage']['enable_parent_canonical_inheritance'])) {
                        $parentCanonicalInheritance = $item['parent_customFields_currentLanguage']['enable_parent_canonical_inheritance'];
                    } elseif (isset($item['parent_customFields_inheritLanguage']['enable_parent_canonical_inheritance']) && is_bool($item['parent_customFields_inheritLanguage']['enable_parent_canonical_inheritance'])) {
                        $parentCanonicalInheritance = $item['parent_customFields_inheritLanguage']['enable_parent_canonical_inheritance'];
                    } elseif (isset($item['parent_customFields_defaultLanguage']['enable_parent_canonical_inheritance']) && is_bool($item['parent_customFields_defaultLanguage']['enable_parent_canonical_inheritance'])) {
                        $parentCanonicalInheritance = $item['parent_customFields_defaultLanguage']['enable_parent_canonical_inheritance'];
                    }
                }
            } else {
                if(empty($item['parent_id'])) {
                    /** Main product */
                    if (isset($item['customFields_currentLanguage']['disable_parent_canonical_inheritance']) && true === $item['customFields_currentLanguage']['disable_parent_canonical_inheritance']) {
                        $parentCanonicalInheritance = false;
                    } elseif (isset($item['customFields_inheritLanguage']['disable_parent_canonical_inheritance']) && true === $item['customFields_inheritLanguage']['disable_parent_canonical_inheritance']) {
                        $parentCanonicalInheritance = false;
                    } elseif (isset($item['customFields_defaultLanguage']['disable_parent_canonical_inheritance']) && true === $item['customFields_defaultLanguage']['disable_parent_canonical_inheritance']) {
                        $parentCanonicalInheritance = false;
                    }
                } else {
                    /** Variant product */
                    if (isset($item['parent_customFields_currentLanguage']['disable_parent_canonical_inheritance']) && true === $item['parent_customFields_currentLanguage']['disable_parent_canonical_inheritance']) {
                        $parentCanonicalInheritance = false;
                    } elseif (isset($item['parent_customFields_inheritLanguage']['disable_parent_canonical_inheritance']) && true === $item['parent_customFields_inheritLanguage']['disable_parent_canonical_inheritance']) {
                        $parentCanonicalInheritance = false;
                    } elseif (isset($item['parent_customFields_defaultLanguage']['disable_parent_canonical_inheritance']) && true === $item['parent_customFields_defaultLanguage']['disable_parent_canonical_inheritance']) {
                        $parentCanonicalInheritance = false;
                    }
                }
            }

            if ($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled && $parentCanonicalInheritance) {
                if(!empty($item['parent_id'])) {
                    /** Set variants inactive in sitemap */
                    $item['sitemapInactive'] = true;
                }
            } else {
                if(empty($item['parent_id']) && !empty($item['child_count'])) {
                    /** Parent product of a variant && NOT canonical inheritance in sitemap */
                    $item['sitemapInactive'] = true;
                }
            }

            $item['sitemapPriority']  = $this->getTranslated($item, 'dreisc_seo_sitemap_priority', true, 0.5);
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
                    ProductEnum::CANONICAL_LINK_TYPE__PRODUCT_URL === $item['dreisc_seo_canonical_link_type'][$context->getSalesChannelId()]['type'] &&
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
                if(!in_array($robotsTag, RobotsTagStruct::VALID_ROBOTS_TAGS, true)) {
                    $robotsTag = $customSetting->getMetaTags()->getRobotsTag()->getDefaultRobotsTagProduct();
                }

                if (!empty($robotsTag) && in_array($robotsTag, [RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW, RobotsTagStruct::ROBOTS_TAG__NOINDEX_NOFOLLOW], true)) {
                    $item['sitemapInactive'] = true;
                }
            }
        }

        $result = array_values(array_filter($result, static function (array $product) {
            return !$product['sitemapInactive'];
        }));

        /** CORE-CHANGES - END  */
        return $result;
    }

    /**
     * @return array<string>
     */
    private function getExcludedProductIds(SalesChannelContext $salesChannelContext): array
    {
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();

        $excludedUrls = $this->configHandler->get(ConfigHandler::EXCLUDED_URLS_KEY);
        if (empty($excludedUrls)) {
            return [];
        }

        $excludedUrls = array_filter($excludedUrls, static function (array $excludedUrl) use ($salesChannelId) {
            if ($excludedUrl['resource'] !== ProductEntity::class) {
                return false;
            }

            if ($excludedUrl['salesChannelId'] !== $salesChannelId) {
                return false;
            }

            return true;
        });

        return array_column($excludedUrls, 'identifier');
    }

    private function groupBySalesChannel($data): array
    {
        if(empty($data)) {
            return [];
        }

        try {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [];
        }

        if(!is_array($data)) {
            return [];
        }

        $rows = [];
        foreach ($data as $item) {
            $salesChannelId = $item['salesChannelId'];

            $rows[$salesChannelId] = $item;
        }

        return $rows;
    }

    private function getTranslated($item, string $field, bool $considerInheritance = true, $defaultValue = null)
    {
        if (isset($item['customFields_currentLanguage'][$field]) && !empty($item['customFields_currentLanguage'][$field])) {
            return $item['customFields_currentLanguage'][$field];
        } elseif (isset($item['customFields_inheritLanguage'][$field]) && !empty($item['customFields_inheritLanguage'][$field])) {
            return $item['customFields_inheritLanguage'][$field];
        } elseif (isset($item['customFields_defaultLanguage'][$field]) && !empty($item['customFields_defaultLanguage'][$field])) {
            return $item['customFields_defaultLanguage'][$field];
        } elseif ($considerInheritance && isset($item['parent_customFields_currentLanguage'][$field]) && !empty($item['parent_customFields_currentLanguage'][$field])) {
            return $item['parent_customFields_currentLanguage'][$field];
        } elseif ($considerInheritance && isset($item['parent_customFields_inheritLanguage'][$field]) && !empty($item['parent_customFields_inheritLanguage'][$field])) {
            return $item['parent_customFields_inheritLanguage'][$field];
        } elseif ($considerInheritance && isset($item['parent_customFields_defaultLanguage'][$field]) && !empty($item['parent_customFields_defaultLanguage'][$field])) {
            return $item['parent_customFields_defaultLanguage'][$field];
        } else {
            return $defaultValue;
        }
    }
}
