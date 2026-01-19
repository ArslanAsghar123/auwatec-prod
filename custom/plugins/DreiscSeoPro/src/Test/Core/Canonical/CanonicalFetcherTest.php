<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Core\Canonical;

use DreiscSeoPro\Core\Canonical\CanonicalFetcherStruct;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Seo\SeoUrlAssembler;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\CategorySeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\LandingpageSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use PHPUnit\Framework\TestCase;
use DreiscSeoPro\Core\Canonical\CanonicalFetcher;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Storefront\Test\Controller\StorefrontControllerTestBehaviour;

class CanonicalFetcherTest extends TestCase
{
    use IntegrationTestBehaviour;
    use BasicTestDataBehaviour;
    use StorefrontControllerTestBehaviour;

    private CanonicalFetcher $canonicalFetcher;
    private ProductSeoDataFetcher $productSeoDataFetcher;
    private CustomSettingLoader $customSettingLoader;

    protected function setUp(): void
    {
        $this->canonicalFetcher = $this->getContainer()->get(CanonicalFetcher::class);
        $this->productSeoDataFetcher = $this->createMock(ProductSeoDataFetcher::class);
        $this->customSettingLoader = $this->createMock(CustomSettingLoader::class);
    }

    private function prepareTestEnvironment(array $customFields = [], bool $parentCanonicalInheritance = false): SalesChannelProductEntity
    {
        $variantProduct = new SalesChannelProductEntity();
        $variantProduct->setId(Uuid::randomHex());
        $variantProduct->setParentId(Uuid::randomHex());
        $variantProduct->setCustomFields($customFields);

        $this->productSeoDataFetcher->method('fetch')->willReturnCallback(
            function (string $productId) use ($variantProduct) {
                return (new SeoDataFetchResultStruct())
                    ->setCanonicalLinkType(ProductEnum::CANONICAL_LINK_TYPE__EXTERNAL_URL)
                    ->setCanonicalLinkReference(
                        $productId === $variantProduct->getId() ? 'MAIN_PRODUCT_LINK' : 'VARIANT_LINK'
                    );
            }
        );

        $customSettingStruct = new CustomSettingStruct([]);
        $customSettingStruct->getCanonical()->getGeneral()->setParentCanonicalInheritance($parentCanonicalInheritance);
        $this->customSettingLoader->method('load')->willReturn($customSettingStruct);

        $this->canonicalFetcher = new CanonicalFetcher(
            $this->productSeoDataFetcher,
            $this->getContainer()->get(CategorySeoDataFetcher::class),
            $this->getContainer()->get(ProductRepository::class),
            $this->getContainer()->get(CategoryRepository::class),
            $this->getContainer()->get(SeoUrlAssembler::class),
            $this->getContainer()->get(LandingpageSeoDataFetcher::class),
            $this->customSettingLoader,
        );

        return $variantProduct;
    }

    private function fetchCanonicalLink(SalesChannelProductEntity $variantProduct): string
    {
        return $this->canonicalFetcher->fetch(
            new CanonicalFetcherStruct(
                ProductDefinition::ENTITY_NAME,
                $variantProduct->getId(),
                $variantProduct,
                $this->getDeDeLanguageId(),
                $this->getSalesChannelId(),
                Uuid::randomHex()
            )
        );
    }

    public function testInactiveParentCanonicalInheritance(): void
    {
        $variantProduct = $this->prepareTestEnvironment();
        $canonicalLink = $this->fetchCanonicalLink($variantProduct);

        self::assertSame('MAIN_PRODUCT_LINK', $canonicalLink);
    }

    public function testParentCanonicalInheritance(): void
    {
        $variantProduct = $this->prepareTestEnvironment([], true);
        $canonicalLink = $this->fetchCanonicalLink($variantProduct);

        self::assertSame('VARIANT_LINK', $canonicalLink);
    }

    public function testParentCanonicalInheritance_with_enableParentCanonicalInheritanceCustomField(): void
    {
        $variantProduct = $this->prepareTestEnvironment(['enable_parent_canonical_inheritance' => true], false);
        $canonicalLink = $this->fetchCanonicalLink($variantProduct);

        self::assertSame('VARIANT_LINK', $canonicalLink);
    }

    public function testParentCanonicalInheritance_with_disableParentCanonicalInheritanceCustomField(): void
    {
        $variantProduct = $this->prepareTestEnvironment(['disable_parent_canonical_inheritance' => true], true);
        $canonicalLink = $this->fetchCanonicalLink($variantProduct);

        self::assertSame('MAIN_PRODUCT_LINK', $canonicalLink);
    }

    public function testParentCanonicalInheritance_with_enableAndDisableParentCanonicalInheritanceCustomFieldMix1(): void
    {
        $variantProduct = $this->prepareTestEnvironment([
            'enable_parent_canonical_inheritance' => true,
            'disable_parent_canonical_inheritance' => true
        ], true);

        $canonicalLink = $this->fetchCanonicalLink($variantProduct);

        self::assertSame('MAIN_PRODUCT_LINK', $canonicalLink);
    }

    public function testParentCanonicalInheritance_with_enableAndDisableParentCanonicalInheritanceCustomFieldMix2(): void
    {
        $variantProduct = $this->prepareTestEnvironment([
            'enable_parent_canonical_inheritance' => true,
            'disable_parent_canonical_inheritance' => true
        ], false);

        $canonicalLink = $this->fetchCanonicalLink($variantProduct);

        self::assertSame('VARIANT_LINK', $canonicalLink);
    }
}
