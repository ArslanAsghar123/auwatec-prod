<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Core\Sitemap;

use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\CustomSettingSaver;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\RobotsTagStruct;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Test\TestCollection;
use PHPUnit\Framework\Attributes\TestWith;
use Shopware\Core\Content\LandingPage\Aggregate\LandingPageTranslation\LandingPageTranslationEntity;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use PHPUnit\Framework\TestCase;
use DreiscSeoPro\Core\Sitemap\LandigpageFetcher;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use function PHPUnit\Framework\assertCount;

/** @see LandigpageFetcher */
class LandigpageFetcherTest extends TestCase
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

    private function getLandingpages(): array
    {
        return (new LandigpageFetcher(
            $this->_getMock(LanguageChainFactory::class),
            $this->_getMock(IteratorFactory::class),
            $this->_getMock(LandingPageDefinition::class),
            $this->_getMock(CustomSettingLoader::class),
        ))->getLandingpages($this->salesChannelContext, 100, 0);
    }

    /**
     * Default behavior
     */
    public function test_default(): void
    {
        $landingpage = $this->_createLandingpage();
        $fetchedLandingpages = $this->getLandingpages();

        self::assertCount(1, $fetchedLandingpages);
        self::assertSame($landingpage->getId(), $fetchedLandingpages[0]['id']);
        self::assertSame(false, $fetchedLandingpages[0]['sitemapInactive']);
        self::assertSame(0.5, $fetchedLandingpages[0]['sitemapPriority']);
    }

    /**
     * Inactive category
     */
    public function test_inactive_category(): void
    {
        $this->_createLandingpage(fn(&$landingpage) => $landingpage['active'] = false);
        $fetchedLandingpages = $this->getLandingpages();

        self::assertCount(0, $fetchedLandingpages);
    }

    /**
     * Three categories
     */
    public function test_multi_categories(): void
    {
        for ($i = 0; $i < 3; $i++)
            $this->_createLandingpage();

        $fetchedLandingpages = $this->getLandingpages();

        self::assertCount(3, $fetchedLandingpages);
    }

    /**
     * Category with custom field dreisc_seo_sitemap_inactive = true
     */
    public function test_customField_sitemapInactive(): void
    {
        $landingpage = $this->_createLandingpage(function (&$landingpage) {
            $landingpage['customFields']['dreisc_seo_sitemap_inactive'] = true;
        });

        self::assertTrue($landingpage->getCustomFields()['dreisc_seo_sitemap_inactive']);

        $fetchedLandingpages = $this->getLandingpages();

        self::assertCount(0, $fetchedLandingpages);
    }

    /**
     * Category with custom field dreisc_seo_sitemap_inactive = true and
     * translation with custom field dreisc_seo_sitemap_inactive = false
     */
    public function test_customField_sitemapInactive_inheritance(): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $landingpage = $this->_createLandingpage(function (&$landingpage) {
            $landingpage['customFields']['dreisc_seo_sitemap_inactive'] = true;
            $landingpage['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_sitemap_inactive'] = false;
        });

        $this->_createLandingpage(function (&$landingpage) {
            $landingpage['customFields']['dreisc_seo_sitemap_inactive'] = false;
            $landingpage['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_sitemap_inactive'] = true;
        });

        $landingpageDeTranslation = $landingpage->getTranslations()->filter(fn (LandingPageTranslationEntity $landingpageTranslation) => $landingpageTranslation->getLanguageId() === $this->getDeDeLanguageId());

        self::assertTrue($landingpage->getCustomFields()['dreisc_seo_sitemap_inactive']);
        self::assertFalse($landingpageDeTranslation->first()->getCustomFields()['dreisc_seo_sitemap_inactive']);

        $fetchedLandingpages = $this->getLandingpages();
        $fetchedCategoryIds = array_map(fn($landingpage) => $landingpage['id'], $fetchedLandingpages);

        self::assertCount(1, $fetchedLandingpages);
        self::assertContains($landingpage->getId(), $fetchedCategoryIds);
    }

    /**
     * Category with custom field dreisc_seo_sitemap_priority = 0.8
     */
    public function test_customField_sitemapPriority(): void
    {
        $landingpage = $this->_createLandingpage(function (&$landingpage) {
            $landingpage['customFields']['dreisc_seo_sitemap_priority'] = 0.8;
        });

        self::assertSame(0.8, $landingpage->getCustomFields()['dreisc_seo_sitemap_priority']);

        $fetchedLandingpages = $this->getLandingpages();

        self::assertSame($landingpage->getId(), $fetchedLandingpages[0]['id']);
        self::assertSame(false, $fetchedLandingpages[0]['sitemapInactive']);
        self::assertSame(0.8, $fetchedLandingpages[0]['sitemapPriority']);
    }

    /**
     * Category with custom field dreisc_seo_sitemap_priority = 0.8 and
     * translation with custom field dreisc_seo_sitemap_priority = 0.7
     */
    public function test_customField_sitemapPriority_inheritance(): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $landingpage = $this->_createLandingpage(function (&$landingpage) {
            $landingpage['customFields']['dreisc_seo_sitemap_priority'] = 0.8;
            $landingpage['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_sitemap_priority'] = 0.7;
        }, $this->salesChannelContext);

        $landingpageDeTranslation = $landingpage->getTranslations()->filter(fn (LandingPageTranslationEntity $landingpageTranslation) => $landingpageTranslation->getLanguageId() === $this->getDeDeLanguageId());

        self::assertSame(0.8, $landingpage->getCustomFields()['dreisc_seo_sitemap_priority']);
        self::assertSame(0.7, $landingpageDeTranslation->first()->getCustomFields()['dreisc_seo_sitemap_priority']);

        $fetchedLandingpages = $this->getLandingpages();

        self::assertSame($landingpage->getId(), $fetchedLandingpages[0]['id']);
        self::assertSame(false, $fetchedLandingpages[0]['sitemapInactive']);
        self::assertSame(0.7, $fetchedLandingpages[0]['sitemapPriority']);
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

        $this->_createLandingpage(fn(&$landingpage) => $landingpage['id'] = Uuid::fromStringToHex('normal-category'));

        $this->_createLandingpage(function (&$landingpageA){
            $landingpageA['id'] = Uuid::fromStringToHex('category-a');
            $landingpageA['customFields']['dreisc_seo_canonical_link_type'] = 'ExternalUrl';
            $landingpageA['customFields']['dreisc_seo_canonical_link_reference'] = 'extern';
        });

        $this->_createLandingpage(function (&$landingpageC){
            $landingpageC['id'] = Uuid::fromStringToHex('category-c');
            $landingpageC['customFields']['dreisc_seo_canonical_link_type'] = 'CategoryUrl';
            $landingpageC['customFields']['dreisc_seo_canonical_link_reference'] = Uuid::fromStringToHex('category-b');
        });

        $this->_createLandingpage(function (&$landingpage){
            $landingpage['id'] = Uuid::fromStringToHex('invalid-canonical-config-category');
            $landingpage['customFields']['dreisc_seo_canonical_link_type'] = 'Den SEO Pfad als Canonical Link ausgeben';
            $landingpage['customFields']['dreisc_seo_canonical_link_reference'] = 'null';
        });

        $fetchedLandingpages = $this->getLandingpages();
        $fetchedCategoryIds = array_map(fn($landingpage) => $landingpage['id'], $fetchedLandingpages);

        if ($hideInSitemapIfSeoUrlNotEqualCanonical) {
            assertCount(2, $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('normal-category'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('invalid-canonical-config-category'), $fetchedCategoryIds);
        } else {
            assertCount(4, $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('normal-category'), $fetchedCategoryIds);
            self::assertContains(Uuid::fromStringToHex('category-a'), $fetchedCategoryIds);
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

        $this->_createLandingpage(fn(&$landingPage) => $landingPage['id'] = Uuid::fromStringToHex('normal-category'));

        $this->_createLandingpage(function (&$landingPage){
            $landingPage['id'] = Uuid::fromStringToHex('invalid-robots-tag');
            $landingPage['customFields']['dreisc_seo_robots_tag'] = 'invalid-robots-tag';
        });

        $this->_createLandingpage(function (&$landingPage){
            $landingPage['id'] = Uuid::fromStringToHex('noindex-follow');
            $landingPage['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW;
        });

        $this->_createLandingpage(function (&$landingPage){
            $landingPage['id'] = Uuid::fromStringToHex('noindex-nofollow');
            $landingPage['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_NOFOLLOW;
        });

        $this->_createLandingpage(function (&$landingPage){
            $landingPage['id'] = Uuid::fromStringToHex('index-follow');
            $landingPage['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW;
        });

        $this->_createLandingpage(function (&$landingPage){
            $landingPage['id'] = Uuid::fromStringToHex('noindex-follow-inherit');
            $landingPage['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW;
        });

        $fetchedLandingPages = $this->getLandingpages();
        $fetchedLandingPageIds = array_map(fn($landingPage) => $landingPage['id'], $fetchedLandingPages);

        if ($hideInSitemapIfRobotsTagNoindex) {
            self::assertContains(Uuid::fromStringToHex('index-follow'), $fetchedLandingPageIds);
            self::assertNotContains(Uuid::fromStringToHex('noindex-follow'), $fetchedLandingPageIds);
            self::assertNotContains(Uuid::fromStringToHex('noindex-nofollow'), $fetchedLandingPageIds);
            self::assertNotContains(Uuid::fromStringToHex('noindex-follow-inherit'), $fetchedLandingPageIds);

            if (in_array($defaultRobotsTagProduct, [RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW, RobotsTagStruct::ROBOTS_TAG__INDEX_NOFOLLOW])) {
                self::assertContains(Uuid::fromStringToHex('normal-category'), $fetchedLandingPageIds);
                self::assertContains(Uuid::fromStringToHex('invalid-robots-tag'), $fetchedLandingPageIds);
            } else {
                self::assertNotContains(Uuid::fromStringToHex('normal-category'), $fetchedLandingPageIds);
                self::assertNotContains(Uuid::fromStringToHex('invalid-robots-tag'), $fetchedLandingPageIds);
            }
        } else {
            self::assertContains(Uuid::fromStringToHex('noindex-follow'), $fetchedLandingPageIds);
            self::assertContains(Uuid::fromStringToHex('noindex-nofollow'), $fetchedLandingPageIds);
            self::assertContains(Uuid::fromStringToHex('index-follow'), $fetchedLandingPageIds);
            self::assertContains(Uuid::fromStringToHex('noindex-follow-inherit'), $fetchedLandingPageIds);
            self::assertContains(Uuid::fromStringToHex('normal-category'), $fetchedLandingPageIds);
            self::assertContains(Uuid::fromStringToHex('invalid-robots-tag'), $fetchedLandingPageIds);
        }
    }
}
