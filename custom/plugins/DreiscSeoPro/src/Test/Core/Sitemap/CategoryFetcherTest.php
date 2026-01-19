<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Core\Sitemap;

use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\CustomSettingSaver;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\RobotsTagStruct;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Sitemap\CategoryFetcher;
use DreiscSeoPro\Test\TestCollection;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Sitemap\Service\ConfigHandler;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use function PHPUnit\Framework\assertCount;

/** @see CategoryFetcher */
class CategoryFetcherTest extends TestCase
{
    use TestCollection;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    protected function setUp(): void
    {
        $this->salesChannelContext = $this->_createDefaultSalesChannelContext();
    }

    private function getCategories(): array
    {
        return (new CategoryFetcher(
            $this->_getMock(ConfigHandler::class),
            $this->_getMock(IteratorFactory::class),
            $this->_getMock(SystemConfigService::class),
            $this->_getMock(LanguageChainFactory::class),
            $this->_getMock(CustomSettingLoader::class),
            $this->_getMock(CategoryDefinition::class),
        ))->getCategories($this->salesChannelContext, 100, 0);
    }

    /**
     * Default behavior
     */
    public function test_default(): void
    {
        $category = $this->_createCategory();
        $fetchedCategories = $this->getCategories();

        self::assertCount(1, $fetchedCategories);
        self::assertSame($category->getId(), $fetchedCategories[0]['id']);
        self::assertSame(false, $fetchedCategories[0]['sitemapInactive']);
        self::assertSame(0.5, $fetchedCategories[0]['sitemapPriority']);
    }

    /**
     * Inactive category
     */
    public function test_inactive_category(): void
    {
        $this->_createcategory(fn(&$category) => $category['active'] = false);
        $fetchedCategories = $this->getCategories();

        self::assertCount(0, $fetchedCategories);
    }

    /**
     * Three categories
     */
    public function test_multi_categories(): void
    {
        for ($i = 0; $i < 3; $i++)
            $this->_createCategory();

        $fetchedCategories = $this->getCategories();

        self::assertCount(3, $fetchedCategories);
    }

    /**
     * Category with custom field dreisc_seo_sitemap_inactive = true
     */
    public function test_customField_sitemapInactive(): void
    {
        $category = $this->_createCategory(function (&$category) {
            $category['customFields']['dreisc_seo_sitemap_inactive'] = true;
        });

        self::assertTrue($category->getCustomFields()['dreisc_seo_sitemap_inactive']);

        $fetchedCategories = $this->getCategories();

        self::assertCount(0, $fetchedCategories);
    }

    /**
     * Category with custom field dreisc_seo_sitemap_inactive = true and
     * translation with custom field dreisc_seo_sitemap_inactive = false
     */
    public function test_customField_sitemapInactive_inheritance(): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $category = $this->_createCategory(function (&$category) {
            $category['customFields']['dreisc_seo_sitemap_inactive'] = true;
            $category['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_sitemap_inactive'] = false;
        });

        $this->_createCategory(function (&$category) {
            $category['customFields']['dreisc_seo_sitemap_inactive'] = false;
            $category['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_sitemap_inactive'] = true;
        });

        self::assertTrue($category->getCustomFields()['dreisc_seo_sitemap_inactive']);
        self::assertFalse($category->getTranslations()->filterByLanguageId($this->getDeDeLanguageId())->first()->getCustomFields()['dreisc_seo_sitemap_inactive']);

        $fetchedCategories = $this->getCategories();
        $fetchedCategoryIds = array_map(fn($category) => $category['id'], $fetchedCategories);

        self::assertCount(1, $fetchedCategories);
        self::assertContains($category->getId(), $fetchedCategoryIds);
    }

    /**
     * Category with custom field dreisc_seo_sitemap_priority = 0.8
     */
    public function test_customField_sitemapPriority(): void
    {
        $category = $this->_createCategory(function (&$category) {
            $category['customFields']['dreisc_seo_sitemap_priority'] = 0.8;
        });

        self::assertSame(0.8, $category->getCustomFields()['dreisc_seo_sitemap_priority']);

        $fetchedCategories = $this->getCategories();

        self::assertSame($category->getId(), $fetchedCategories[0]['id']);
        self::assertSame(false, $fetchedCategories[0]['sitemapInactive']);
        self::assertSame(0.8, $fetchedCategories[0]['sitemapPriority']);
    }

    /**
     * Category with custom field dreisc_seo_sitemap_priority = 0.8 and
     * translation with custom field dreisc_seo_sitemap_priority = 0.7
     */
    public function test_customField_sitemapPriority_inheritance(): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $category = $this->_createCategory(function (&$category) {
            $category['customFields']['dreisc_seo_sitemap_priority'] = 0.8;
            $category['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_sitemap_priority'] = 0.7;
        });

        self::assertSame(0.8, $category->getCustomFields()['dreisc_seo_sitemap_priority']);
        self::assertSame(0.7, $category->getTranslations()->filterByLanguageId($this->getDeDeLanguageId())->first()->getCustomFields()['dreisc_seo_sitemap_priority']);

        $fetchedCategories = $this->getCategories();

        self::assertSame($category->getId(), $fetchedCategories[0]['id']);
        self::assertSame(false, $fetchedCategories[0]['sitemapInactive']);
        self::assertSame(0.7, $fetchedCategories[0]['sitemapPriority']);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_hideInSitemapIfSeoUrlNotEqualCanonical($hideInSitemapIfSeoUrlNotEqualCanonical)
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'sitemap' => [
                'general' => [
                    'hideInSitemapIfSeoUrlNotEqualCanonical' => $hideInSitemapIfSeoUrlNotEqualCanonical
                ]
            ]
        ]);
        $customSetting = $this->getContainer()->get(CustomSettingLoader::class)->load();
        self::assertSame($hideInSitemapIfSeoUrlNotEqualCanonical, $customSetting->getSitemap()->getGeneral()->getHideInSitemapIfSeoUrlNotEqualCanonical());

        $this->_createCategory(fn(&$category) => $category['id'] = Uuid::fromStringToHex('normal-category'));

        $this->_createCategory(function (&$categoryA){
            $categoryA['id'] = Uuid::fromStringToHex('category-a');
            $categoryA['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"ExternalUrl"}]';
            $categoryA['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"extern"}]';
        });

        $this->_createCategory(function (&$categoryB){
            $categoryB['id'] = Uuid::fromStringToHex('category-b');
            $categoryB['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"CategoryUrl"}]';
            $categoryB['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"' . Uuid::fromStringToHex('category-b') . '"}]';
        });

        $this->_createCategory(function (&$categoryC){
            $categoryC['id'] = Uuid::fromStringToHex('category-c');
            $categoryC['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"CategoryUrl"}]';
            $categoryC['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"' . Uuid::fromStringToHex('category-b') . '"}]';
        });

        $this->_createCategory(function (&$category){
            $category['id'] = Uuid::fromStringToHex('invalid-canonical-config-category');
            $category['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"Den SEO Pfad als Canonical Link ausgeben"}]';
            $category['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"null"}]';
        });

        $fetchedCategories = $this->getCategories();
        $fetchedCategoryIds = array_map(fn($category) => $category['id'], $fetchedCategories);

        if ($hideInSitemapIfSeoUrlNotEqualCanonical) {
            assertCount(3, $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('normal-category'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('category-b'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('invalid-canonical-config-category'), $fetchedCategoryIds);
        } else {
            assertCount(5, $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('normal-category'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('category-a'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('category-b'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('category-c'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('invalid-canonical-config-category'), $fetchedCategoryIds);
        }
    }

    #[TestWith([true, RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW])]
    #[TestWith([false, RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW])]
    #[TestWith([true, RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW])]
    #[TestWith([false, RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW])]
    public function test_hideInSitemapIfRobotsTagNoindex(bool $hideInSitemapIfRobotsTagNoindex, string $defaultRobotsTagProduct)
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'sitemap' => [
                'general' => [
                    'hideInSitemapIfRobotsTagNoindex' => $hideInSitemapIfRobotsTagNoindex
                ]
            ],
            'metaTags' => [
                'robotsTag' => [
                    'defaultRobotsTagProduct' => $defaultRobotsTagProduct
                ]
            ]
        ]);
        $customSetting = $this->getContainer()->get(CustomSettingLoader::class)->load();
        self::assertSame($hideInSitemapIfRobotsTagNoindex, $customSetting->getSitemap()->getGeneral()->getHideInSitemapIfRobotsTagNoindex());
        self::assertSame($defaultRobotsTagProduct, $customSetting->getMetaTags()->getRobotsTag()->getDefaultRobotsTagProduct());

        $this->_createCategory(fn(&$category) => $category['id'] = Uuid::fromStringToHex('normal-category'));

        $this->_createCategory(function (&$category){
            $category['id'] = Uuid::fromStringToHex('invalid-robots-tag');
            $category['customFields']['dreisc_seo_robots_tag'] = 'invalid-robots-tag';
        });

        $this->_createCategory(function (&$category){
            $category['id'] = Uuid::fromStringToHex('noindex-follow');
            $category['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW;
        });

        $this->_createCategory(function (&$category){
            $category['id'] = Uuid::fromStringToHex('noindex-nofollow');
            $category['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_NOFOLLOW;
        });

        $this->_createCategory(function (&$category){
            $category['id'] = Uuid::fromStringToHex('index-follow');
            $category['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW;
        });

        $this->_createCategory(function (&$category){
            $category['id'] = Uuid::fromStringToHex('noindex-follow-inherit');
            $category['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW;
        });

        $fetchedCategories = $this->getCategories();
        $fetchedCategoryIds = array_map(fn($category) => $category['id'], $fetchedCategories);

        if ($hideInSitemapIfRobotsTagNoindex) {
            self::assertContains(Uuid::fromStringToHex('index-follow'), $fetchedCategoryIds);
            self::assertNotContains(Uuid::fromStringToHex('noindex-follow'), $fetchedCategoryIds);
            self::assertNotContains(Uuid::fromStringToHex('noindex-nofollow'), $fetchedCategoryIds);
            self::assertNotContains(Uuid::fromStringToHex('noindex-follow-inherit'), $fetchedCategoryIds);

            if (in_array($defaultRobotsTagProduct, [RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW, RobotsTagStruct::ROBOTS_TAG__INDEX_NOFOLLOW])) {
                self::assertContains(Uuid::fromStringToHex('normal-category'), $fetchedCategoryIds);
                self::assertContains(Uuid::fromStringToHex('invalid-robots-tag'), $fetchedCategoryIds);
            } else {
                self::assertNotContains(Uuid::fromStringToHex('normal-category'), $fetchedCategoryIds);
                self::assertNotContains(Uuid::fromStringToHex('invalid-robots-tag'), $fetchedCategoryIds);
            }
        } else {
            self::assertContains(Uuid::fromStringToHex('noindex-follow'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('noindex-nofollow'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('index-follow'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('noindex-follow-inherit'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('normal-category'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('invalid-robots-tag'), $fetchedCategoryIds);
        }
    }
}
