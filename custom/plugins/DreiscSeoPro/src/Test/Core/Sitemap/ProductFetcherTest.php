<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Core\Sitemap;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\CustomSettingSaver;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\RobotsTagStruct;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Decorator\Core\Content\Sitemap\Provider\ProductUrlProvider;
use DreiscSeoPro\Test\Behaviour\BehaviourCollection;
use DreiscSeoPro\Test\TestCollection;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use DreiscSeoPro\Core\Sitemap\ProductFetcher;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Sitemap\Service\ConfigHandler;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use function PHPUnit\Framework\assertCount;

/** @see ProductFetcher */
class ProductFetcherTest extends TestCase
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

    private function getProducts(): array
    {
        return (new ProductFetcher(
            $this->_getMock(ConfigHandler::class),
            $this->_getMock(Connection::class),
            $this->_getMock(ProductDefinition::class),
            $this->_getMock(IteratorFactory::class),
            $this->_getMock(SystemConfigService::class),
            $this->_getMock(LanguageChainFactory::class),
            $this->_getMock(CustomSettingLoader::class),
        ))->getProducts($this->salesChannelContext, 100, 0);
    }

    private function setSystemConfigReturnValue($key, $value): void
    {
        $this->_createMock(SystemConfigService::class)
            ->method('get')
            ->with($key)
            ->willReturn($value);
    }

    /**
     * Default behavior
     */
    public function test_default(): void
    {
        $product = $this->_createProduct();
        $fetchedProducts = $this->getProducts();

        self::assertCount(1, $fetchedProducts);
        self::assertSame($product->getId(), $fetchedProducts[0]['id']);
        self::assertSame($product->getProductNumber(), $fetchedProducts[0]['product_number']);
        self::assertSame(false, $fetchedProducts[0]['sitemapInactive']);
        self::assertSame(0.5, $fetchedProducts[0]['sitemapPriority']);
    }

    /**
     * Inactive product
     */
    public function test_inactive_product(): void
    {
        $this->_createProduct(fn(&$product) => $product['active'] = false);
        $fetchedProducts = $this->getProducts();

        self::assertCount(0, $fetchedProducts);
    }

    /**
     * Three products
     */
    public function test_multi_products(): void
    {
        for ($i = 0; $i < 3; $i++)
            $this->_createProduct();

        $fetchedProducts = $this->getProducts();

        self::assertCount(3, $fetchedProducts);
    }

    /**
     * CONFIG_HIDE_AFTER_CLOSEOUT = true / Closeout product without stock
     */
    public function test_hideAfterCloseout01(): void
    {
        $this->setSystemConfigReturnValue(ProductUrlProvider::CONFIG_HIDE_AFTER_CLOSEOUT, true);

        $product = $this->_createProduct(function (&$product) {
            $product['stock'] = 0;
            $product['isCloseout'] = true;
        });

        self::assertSame(0, $product->getStock());
        self::assertTrue($product->getIsCloseout());

        $fetchedProducts = $this->getProducts();

        self::assertCount(0, $fetchedProducts);
    }

    /**
     * CONFIG_HIDE_AFTER_CLOSEOUT = false / Closeout product without stock
     */
    public function test_hideAfterCloseout02(): void
    {
        $this->setSystemConfigReturnValue(ProductUrlProvider::CONFIG_HIDE_AFTER_CLOSEOUT, false);

        $product = $this->_createProduct(function (&$product) {
            $product['stock'] = 0;
            $product['isCloseout'] = true;
        });

        self::assertSame(0, $product->getStock());
        self::assertTrue($product->getIsCloseout());

        $fetchedProducts = $this->getProducts();

        self::assertCount(1, $fetchedProducts);
        self::assertSame($product->getId(), $fetchedProducts[0]['id']);
        self::assertSame($product->getProductNumber(), $fetchedProducts[0]['product_number']);
        self::assertSame(false, $fetchedProducts[0]['sitemapInactive']);
        self::assertSame(0.5, $fetchedProducts[0]['sitemapPriority']);
    }

    /**
     * Product with custom field dreisc_seo_sitemap_inactive = true
     */
    public function test_customField_sitemapInactive(): void
    {
        $product = $this->_createProduct(function (&$product) {
            $product['customFields']['dreisc_seo_sitemap_inactive'] = true;
        });

        self::assertTrue($product->getCustomFields()['dreisc_seo_sitemap_inactive']);

        $fetchedProducts = $this->getProducts();

        self::assertCount(0, $fetchedProducts);
    }

    /**
     * Product with custom field dreisc_seo_sitemap_inactive = true and
     * translation with custom field dreisc_seo_sitemap_inactive = false
     */
    public function test_customField_sitemapInactive_inheritance(): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $product = $this->_createProduct(function (&$product) {
            $product['customFields']['dreisc_seo_sitemap_inactive'] = true;
            $product['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_sitemap_inactive'] = false;
        });

        self::assertTrue($product->getCustomFields()['dreisc_seo_sitemap_inactive']);
        self::assertFalse($product->getTranslations()->filterByLanguageId($this->getDeDeLanguageId())->first()->getCustomFields()['dreisc_seo_sitemap_inactive']);

        $fetchedProducts = $this->getProducts();

        self::assertCount(1, $fetchedProducts);
    }

    /**
     * Product with custom field dreisc_seo_sitemap_priority = 0.8
     */
    public function test_customField_sitemapPriority(): void
    {
        $product = $this->_createProduct(function (&$product) {
            $product['customFields']['dreisc_seo_sitemap_priority'] = 0.8;
        });

        self::assertSame(0.8, $product->getCustomFields()['dreisc_seo_sitemap_priority']);

        $fetchedProducts = $this->getProducts();

        self::assertSame($product->getId(), $fetchedProducts[0]['id']);
        self::assertSame($product->getProductNumber(), $fetchedProducts[0]['product_number']);
        self::assertSame(false, $fetchedProducts[0]['sitemapInactive']);
        self::assertSame(0.8, $fetchedProducts[0]['sitemapPriority']);
    }

    /**
     * Product with custom field dreisc_seo_sitemap_priority = 0.8 and
     * translation with custom field dreisc_seo_sitemap_priority = 0.7
     */
    public function test_customField_sitemapPriority_inheritance(): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $product = $this->_createProduct(function (&$product) {
            $product['customFields']['dreisc_seo_sitemap_priority'] = 0.8;
            $product['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_sitemap_priority'] = 0.7;
        });

        self::assertSame(0.8, $product->getCustomFields()['dreisc_seo_sitemap_priority']);
        self::assertSame(0.7, $product->getTranslations()->filterByLanguageId($this->getDeDeLanguageId())->first()->getCustomFields()['dreisc_seo_sitemap_priority']);

        $fetchedProducts = $this->getProducts();

        self::assertSame($product->getId(), $fetchedProducts[0]['id']);
        self::assertSame($product->getProductNumber(), $fetchedProducts[0]['product_number']);
        self::assertSame(false, $fetchedProducts[0]['sitemapInactive']);
        self::assertSame(0.7, $fetchedProducts[0]['sitemapPriority']);
    }

    /**
     * Load one product with 4 variants
     */
    public function test_variant_product(): void
    {
        $product = $this->_createVariantProduct();
        $fetchedProducts = $this->getProducts();

        self::assertCount(4, $fetchedProducts);

        /** Check if the values of the array $variantProductIds and $fetchedProductIds are the same */
        $variantProductIds = $product->getChildren()->map(fn($child) => $child->getId());
        $fetchedProductIds = array_map(fn($product) => $product['id'], $fetchedProducts);

        $variantProductIds = array_values($variantProductIds);
        asort($variantProductIds);

        $fetchedProductIds = array_values($fetchedProductIds);
        asort($fetchedProductIds);

        self::assertSame($variantProductIds, $fetchedProductIds);
    }

    /**
     * Test with the custom setting "parentUrlInSitemapIfParentCanonicalInheritance" enabled
     */
    #[TestWith([true, true])]
    #[TestWith([true, false])]
    #[TestWith([false, true])]
    #[TestWith([false, false])]
    public function test_with_parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled($parentCanonicalInheritance, $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled): void
    {
        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'canonical' => [
                'general' => [
                    'parentCanonicalInheritance' => $parentCanonicalInheritance
                ]
            ],
            'sitemap' => [
                'general' => [
                    'parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled' => $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled
                ]
            ]
        ]);

        $customSetting = $this->getContainer()->get(CustomSettingLoader::class)->load();
        self::assertSame($parentCanonicalInheritance, $customSetting->getCanonical()->getGeneral()->getParentCanonicalInheritance());
        self::assertSame($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled, $customSetting->getSitemap()->getGeneral()->getParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled());

        /** We create a product with four variants and another one */
        $product = $this->_createVariantProduct();
        $otherNormalProduct = $this->_createProduct();

        /** Fetch products for sitemap */
        $fetchedProducts = $this->getProducts();

        if ($parentCanonicalInheritance && $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled) {
            self::assertCount(2, $fetchedProducts);
        } else {
            /** We have 4 variants without main product and the other product */
            self::assertCount(5, $fetchedProducts);
        }

        $fetchedProductIds = array_map(fn($product) => $product['id'], $fetchedProducts);
        $variantProductIds = $product->getChildren()->map(fn($child) => $child->getId());
        if ($parentCanonicalInheritance && $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled) {
            /** Parent product should be in the sitemap */
            self::assertContains($product->getId(), $fetchedProductIds);
            /** Variant ids should NOT be in the sitemap */
            foreach($variantProductIds as $variantProductId) {
                self::assertNotContains($variantProductId, $fetchedProductIds);
            }
        } else {
            /** Parent product should NOT be in the sitemap */
            self::assertNotContains($product->getId(), $fetchedProductIds);
            /** Variant ids should be in the sitemap */
            foreach($variantProductIds as $variantProductId) {
                self::assertContains($variantProductId, $fetchedProductIds);
            }
        }

        /** Check for the other product */
        self::assertContains($otherNormalProduct->getId(), $fetchedProductIds);
    }

    /** Parent canonical inheritance is disabled globally but activated for the product */
    #[TestWith([false, true])]
    #[TestWith([false, false])]
    #[TestWith([true, true])]
    #[TestWith([true, false])]
    public function test_with_customField_enableParentCanonicalInheritance($parentCanonicalInheritance, $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled): void
    {
        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'canonical' => [
                'general' => [
                    'parentCanonicalInheritance' => $parentCanonicalInheritance
                ]
            ],
            'sitemap' => [
                'general' => [
                    'parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled' => $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled
                ]
            ]
        ]);

        $customSetting = $this->getContainer()->get(CustomSettingLoader::class)->load();
        self::assertSame($parentCanonicalInheritance, $customSetting->getCanonical()->getGeneral()->getParentCanonicalInheritance());
        self::assertSame($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled, $customSetting->getSitemap()->getGeneral()->getParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled());

        /** enable_parent_canonical_inheritance = true in the system language */
        $productSystemLanguage = $this->_createVariantProduct(function (&$product) {
            $product['customFields']['enable_parent_canonical_inheritance'] = true;
        });

        /** Normal product */
        $otherNormalProduct = $this->_createProduct();

        /** Fetch products for sitemap */
        $fetchedProducts = $this->getProducts();

        /** Check explicit for the ids */
        $fetchedProductIds = array_map(fn($product) => $product['id'], $fetchedProducts);
        if($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled) {
            self::assertContains($productSystemLanguage->getId(), $fetchedProductIds); /** Parent product should be in the sitemap */
        } else {
            self::assertNotContains($productSystemLanguage->getId(), $fetchedProductIds); /** Parent product should NOT be in the sitemap */
        }
        self::assertContains($otherNormalProduct->getId(), $fetchedProductIds); /** Other product should be in the sitemap */

        $variantsProductSystemLanguage = $productSystemLanguage->getChildren()->map(fn($child) => $child->getId());
        foreach($variantsProductSystemLanguage as $variantProductSystemLanguageId) {
            if($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled) {
                /** Variant ids should NOT be in the sitemap */
                self::assertNotContains($variantProductSystemLanguageId, $fetchedProductIds);
            } else {
                /** Variant ids should be in the sitemap */
                self::assertContains($variantProductSystemLanguageId, $fetchedProductIds);
            }
        }

        if($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled) {
            /** Check count: 1 (product system language) + 1 (other product) */
            self::assertCount(2, $fetchedProducts);
        } else {
            /** Check count: 4 (variants) + 1 (other product) */
            self::assertCount(5, $fetchedProducts);
        }
    }

    /** Parent canonical inheritance is disabled globally but activated for the product */
    #[TestWith([false, true])]
    #[TestWith([false, false])]
    #[TestWith([true, true])]
    #[TestWith([true, false])]
    public function test_with_customField_enableParentCanonicalInheritance_currentLanguage($parentCanonicalInheritance, $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'canonical' => [
                'general' => [
                    'parentCanonicalInheritance' => $parentCanonicalInheritance
                ]
            ],
            'sitemap' => [
                'general' => [
                    'parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled' => $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled
                ]
            ]
        ]);

        $customSetting = $this->getContainer()->get(CustomSettingLoader::class)->load();
        self::assertSame($parentCanonicalInheritance, $customSetting->getCanonical()->getGeneral()->getParentCanonicalInheritance());
        self::assertSame($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled, $customSetting->getSitemap()->getGeneral()->getParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled());

        /** enable_parent_canonical_inheritance = true in the system language */
        $productSystemLanguage = $this->_createVariantProduct(function (&$product) {
            $product['translations'][$this->getDeDeLanguageId()]['customFields']['enable_parent_canonical_inheritance'] = true;
        });

        /** Normal product */
        $otherNormalProduct = $this->_createProduct();

        /** Fetch products for sitemap */
        $fetchedProducts = $this->getProducts();

        /** Check explicit for the ids */
        $fetchedProductIds = array_map(fn($product) => $product['id'], $fetchedProducts);
        if($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled) {
            self::assertContains($productSystemLanguage->getId(), $fetchedProductIds); /** Parent product should be in the sitemap */
        } else {
            self::assertNotContains($productSystemLanguage->getId(), $fetchedProductIds); /** Parent product should NOT be in the sitemap */
        }
        self::assertContains($otherNormalProduct->getId(), $fetchedProductIds); /** Other product should be in the sitemap */

        $variantsProductSystemLanguage = $productSystemLanguage->getChildren()->map(fn($child) => $child->getId());
        foreach($variantsProductSystemLanguage as $variantProductSystemLanguageId) {
            if($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled) {
                /** Variant ids should NOT be in the sitemap */
                self::assertNotContains($variantProductSystemLanguageId, $fetchedProductIds);
            } else {
                /** Variant ids should be in the sitemap */
                self::assertContains($variantProductSystemLanguageId, $fetchedProductIds);
            }
        }

        if($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled) {
            /** Check count: 1 (product system language) + 1 (other product) */
            self::assertCount(2, $fetchedProducts);
        } else {
            /** Check count: 4 (variants) + 1 (other product) */
            self::assertCount(5, $fetchedProducts);
        }
    }

    /** Fix: enable_parent_canonical_inheritance = false was executed like the true case */
    public function test_with_customField_enableParentCanonicalInheritance_mixedInheritance(): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'canonical' => [
                'general' => [
                    'parentCanonicalInheritance' => false
                ]
            ],
            'sitemap' => [
                'general' => [
                    'parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled' => true
                ]
            ]
        ]);

        $customSetting = $this->getContainer()->get(CustomSettingLoader::class)->load();
        self::assertFalse($customSetting->getCanonical()->getGeneral()->getParentCanonicalInheritance());
        self::assertTrue($customSetting->getSitemap()->getGeneral()->getParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled());

        $productSystemLanguage = $this->_createVariantProduct(function (&$product) {
            $product['customFields']['enable_parent_canonical_inheritance'] = true;
            $product['translations'][$this->getDeDeLanguageId()]['customFields']['enable_parent_canonical_inheritance'] = false;
        });

        /** Fetch products for sitemap */
        $fetchedProducts = $this->getProducts();

        /** Check explicit for the ids */
        $fetchedProductIds = array_map(fn($product) => $product['id'], $fetchedProducts);
        self::assertNotContains($productSystemLanguage->getId(), $fetchedProductIds); /** Parent product should NOT be in the sitemap */

        $variantsProductSystemLanguage = $productSystemLanguage->getChildren()->map(fn($child) => $child->getId());
        foreach($variantsProductSystemLanguage as $variantProductSystemLanguageId) {
            /** Variant ids should be in the sitemap */
            self::assertContains($variantProductSystemLanguageId, $fetchedProductIds);
        }

        /** Check count: 4 (variants) */
        self::assertCount(4, $fetchedProducts);
    }

    /** Fix: enable_parent_canonical_inheritance = false was executed like the true case */
    public function test_with_customField_enableParentCanonicalInheritance_fix01(): void
    {
        $this->salesChannelContext = $this->_createDeLanguageSalesChannelContext();

        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'canonical' => [
                'general' => [
                    'parentCanonicalInheritance' => false
                ]
            ],
            'sitemap' => [
                'general' => [
                    'parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled' => true
                ]
            ]
        ]);

        $customSetting = $this->getContainer()->get(CustomSettingLoader::class)->load();
        self::assertFalse($customSetting->getCanonical()->getGeneral()->getParentCanonicalInheritance());
        self::assertTrue($customSetting->getSitemap()->getGeneral()->getParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled());

        $productSystemLanguage = $this->_createVariantProduct(function (&$product) {
            $product['translations'][$this->getDeDeLanguageId()]['customFields']['enable_parent_canonical_inheritance'] = false;
        });

        /** Fetch products for sitemap */
        $fetchedProducts = $this->getProducts();

        /** Check explicit for the ids */
        $fetchedProductIds = array_map(fn($product) => $product['id'], $fetchedProducts);
        self::assertNotContains($productSystemLanguage->getId(), $fetchedProductIds); /** Parent product should NOT be in the sitemap */

        $variantsProductSystemLanguage = $productSystemLanguage->getChildren()->map(fn($child) => $child->getId());
        foreach($variantsProductSystemLanguage as $variantProductSystemLanguageId) {
            /** Variant ids should be in the sitemap */
            self::assertContains($variantProductSystemLanguageId, $fetchedProductIds);
        }

        /** Check count: 4 (variants) */
        self::assertCount(4, $fetchedProducts);
    }

    /** Parent canonical inheritance is enabled globally but deactivated for the product */
    #[TestWith([false, true])]
    #[TestWith([false, false])]
    #[TestWith([true, true])]
    #[TestWith([true, false])]
    public function test_with_customField_disableParentCanonicalInheritance($parentCanonicalInheritance, $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled): void
    {
        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'canonical' => [
                'general' => [
                    'parentCanonicalInheritance' => $parentCanonicalInheritance
                ]
            ],
            'sitemap' => [
                'general' => [
                    'parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled' => $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled
                ]
            ]
        ]);

        $customSetting = $this->getContainer()->get(CustomSettingLoader::class)->load();
        self::assertSame($parentCanonicalInheritance, $customSetting->getCanonical()->getGeneral()->getParentCanonicalInheritance());
        self::assertSame($parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled, $customSetting->getSitemap()->getGeneral()->getParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled());

        /** enable_parent_canonical_inheritance = true in the system language */
        $productSystemLanguage = $this->_createVariantProduct(function (&$product) {
            $product['customFields']['disable_parent_canonical_inheritance'] = true;
        });

        /** Normal product */
        $otherNormalProduct = $this->_createProduct();

        /** Fetch products for sitemap */
        $fetchedProducts = $this->getProducts();

        /** Check explicit for the ids */
        $fetchedProductIds = array_map(fn($product) => $product['id'], $fetchedProducts);

        self::assertNotContains($productSystemLanguage->getId(), $fetchedProductIds); /** Parent product should NOT be in the sitemap */
        self::assertContains($otherNormalProduct->getId(), $fetchedProductIds); /** Other product should be in the sitemap */

        $variantsProductSystemLanguage = $productSystemLanguage->getChildren()->map(fn($child) => $child->getId());
        foreach($variantsProductSystemLanguage as $variantProductSystemLanguageId) {
            /** Variant ids should be in the sitemap */
            self::assertContains($variantProductSystemLanguageId, $fetchedProductIds);
        }

        /** Check count: 4 (variants) + 1 (other product) */
        self::assertCount(5, $fetchedProducts);
    }

    /** Ensure that the URL is not included in the sitemap if the standard defines the output of the canonical URL of another variant */
    #[TestWith([true])]
    #[TestWith([false])]
    public function test_canonicalProductId($activeCanonicalProductId)
    {
        $product = $this->_createVariantProduct(function (&$product) use ($activeCanonicalProductId){
            $product['children'][0]['id'] = Uuid::fromStringToHex('variant01');
            $product['children'][1]['id'] = Uuid::fromStringToHex('variant02');
            $product['children'][2]['id'] = Uuid::fromStringToHex('variant03');
            $product['children'][3]['id'] = Uuid::fromStringToHex('variant04');
        });

        if ($activeCanonicalProductId) {
            $this->getContainer()->get(ProductRepository::class)->upsert([
                [
                    'id' => $product->getId(),
                    'canonicalProductId' => Uuid::fromStringToHex('variant02')
                ]
            ]);
        }

        $fetchedProducts = $this->getProducts();
        $fetchedProductIds = array_map(fn($product) => Uuid::fromBytesToHex($product['product_id']), $fetchedProducts);

        if ($activeCanonicalProductId) {
            assertCount(1, $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variant02'), $fetchedProductIds);
        } else {
            assertCount(4, $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variant01'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variant02'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variant03'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variant04'), $fetchedProductIds);
        }
    }

    /** Make sure that the option enableParentCanonicalInheritance is also effective if a canonicalProductId is still stored */
    public function test_canonicalProductId_with_enableParentCanonicalInheritance()
    {
        $this->getContainer()->get(CustomSettingSaver::class)->save([
            'sitemap' => [
                'general' => [
                    'parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled' => true
                ]
            ]
        ]);

        $product = $this->_createVariantProduct(function (&$product){
            $product['customFields']['enable_parent_canonical_inheritance'] = true;

            $product['children'][0]['id'] = Uuid::fromStringToHex('variant01');
            $product['children'][1]['id'] = Uuid::fromStringToHex('variant02');
            $product['children'][2]['id'] = Uuid::fromStringToHex('variant03');
            $product['children'][3]['id'] = Uuid::fromStringToHex('variant04');
        });

        $this->getContainer()->get(ProductRepository::class)->upsert([
            [
                'id' => $product->getId(),
                'canonicalProductId' => Uuid::fromStringToHex('variant02')
            ]
        ]);


        $fetchedProducts = $this->getProducts();
        assertCount(1, $fetchedProducts);
        self::assertSame($product->getId(), $fetchedProducts[0]['id']);
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

        $this->_createProduct(fn(&$product) => $product['id'] = Uuid::fromStringToHex('normal-product'));

        $this->_createVariantProduct(function (&$productA){
            $productA['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"ExternalUrl"}]';
            $productA['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"extern"}]';

            $productA['children'][0]['id'] = Uuid::fromStringToHex('variantA-01');
            $productA['children'][1]['id'] = Uuid::fromStringToHex('variantA-02');
            $productA['children'][2]['id'] = Uuid::fromStringToHex('variantA-03');
            $productA['children'][3]['id'] = Uuid::fromStringToHex('variantA-04');
        });

        $this->_createVariantProduct(function (&$productB){
            $productB['children'][0]['id'] = Uuid::fromStringToHex('variantB-01');
            $productB['children'][0]['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"ExternalUrl"}]';
            $productB['children'][0]['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"extern"}]';

            $productB['children'][1]['id'] = Uuid::fromStringToHex('variantB-02');
            $productB['children'][1]['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"ProductUrl"}]';
            $productB['children'][1]['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"' . Uuid::fromStringToHex('variantB-02') . '"}]';

            $productB['children'][2]['id'] = Uuid::fromStringToHex('variantB-03');
            $productB['children'][2]['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"ProductUrl"}]';
            $productB['children'][2]['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"' . Uuid::fromStringToHex('variantB-02') . '"}]';

            $productB['children'][3]['id'] = Uuid::fromStringToHex('variantB-04');
            $productB['children'][3]['translations'][Defaults::LANGUAGE_SYSTEM]['customFields'] = [];
            $productB['children'][3]['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"ExternalUrl"}]';
            $productB['children'][3]['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"extern"}]';
        });

        $this->_createProduct(function (&$productC) {
            $productC['id'] = Uuid::fromStringToHex('invalid-canonical-config-product');
            $productC['customFields']['dreisc_seo_canonical_link_type'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","type":"Den SEO Pfad als Canonical Link ausgeben"}]';
            $productC['customFields']['dreisc_seo_canonical_link_reference'] = '[{"salesChannelId":"' . $this->salesChannelContext->getSalesChannelId() . '","reference":"null"}]';
        });

        $fetchedProducts = $this->getProducts();
        $fetchedProductIds = array_map(fn($product) => Uuid::fromBytesToHex($product['product_id']), $fetchedProducts);

        if ($hideInSitemapIfSeoUrlNotEqualCanonical) {
            assertCount(3, $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('normal-product'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-02'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('invalid-canonical-config-product'), $fetchedProductIds);
        } else {
            assertCount(10, $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('normal-product'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantA-01'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantA-02'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantA-03'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantA-04'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-01'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-02'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-03'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-04'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('invalid-canonical-config-product'), $fetchedProductIds);
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

        $this->_createProduct(fn(&$product) => $product['id'] = Uuid::fromStringToHex('normal-product'));

        $this->_createProduct(function (&$product){
            $product['id'] = Uuid::fromStringToHex('invalid-robots-tag');
            $product['customFields']['dreisc_seo_robots_tag'] = 'invalid-robots-tag';
        });

        $this->_createVariantProduct(function (&$productA){
            $productA['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW;

            $productA['children'][0]['id'] = Uuid::fromStringToHex('variantA-01');
            $productA['children'][1]['id'] = Uuid::fromStringToHex('variantA-02');
            $productA['children'][2]['id'] = Uuid::fromStringToHex('variantA-03');
            $productA['children'][3]['id'] = Uuid::fromStringToHex('variantA-04');
        });

        $this->_createVariantProduct(function (&$productB){
            $productB['children'][0]['id'] = Uuid::fromStringToHex('variantB-01');
            $productB['children'][0]['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW;

            $productB['children'][1]['id'] = Uuid::fromStringToHex('variantB-02');
            $productB['children'][1]['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_NOFOLLOW;

            $productB['children'][2]['id'] = Uuid::fromStringToHex('variantB-03');
            $productB['children'][2]['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW;

            $productB['children'][3]['id'] = Uuid::fromStringToHex('variantB-04');
            $productB['children'][3]['translations'][Defaults::LANGUAGE_SYSTEM]['customFields'] = [];
            $productB['children'][3]['translations'][$this->getDeDeLanguageId()]['customFields']['dreisc_seo_robots_tag'] = RobotsTagStruct::ROBOTS_TAG__NOINDEX_FOLLOW;
        });

        $fetchedProducts = $this->getProducts();
        $fetchedProductIds = array_map(fn($product) => Uuid::fromBytesToHex($product['product_id']), $fetchedProducts);

        if ($hideInSitemapIfRobotsTagNoindex) {
            self::assertContains(Uuid::fromStringToHex('variantB-03'), $fetchedProductIds);

            if (in_array($defaultRobotsTagProduct, [RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW, RobotsTagStruct::ROBOTS_TAG__INDEX_NOFOLLOW])) {
                self::assertContains(Uuid::fromStringToHex('normal-product'), $fetchedProductIds);
                self::assertContains(Uuid::fromStringToHex('invalid-robots-tag'), $fetchedProductIds);
                assertCount(3, $fetchedProductIds);
            } else {
                assertCount(1, $fetchedProductIds);
            }
        } else {
            self::assertContains(Uuid::fromStringToHex('variantA-01'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantA-02'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantA-03'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantA-04'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-01'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-02'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-03'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('variantB-04'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('normal-product'), $fetchedProductIds);
            self::assertContains(Uuid::fromStringToHex('invalid-robots-tag'), $fetchedProductIds);
            assertCount(10, $fetchedProductIds);
        }
    }
}
