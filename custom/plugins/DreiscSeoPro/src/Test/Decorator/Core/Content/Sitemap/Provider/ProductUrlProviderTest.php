<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Decorator\Core\Content\Sitemap\Provider;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Sitemap\ProductFetcher;
use DreiscSeoPro\Test\TestCollection;
use PHPUnit\Framework\TestCase;
use DreiscSeoPro\Decorator\Core\Content\Sitemap\Provider\ProductUrlProvider;
use Shopware\Core\Content\Sitemap\Struct\UrlResult;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;

class ProductUrlProviderTest extends TestCase
{
    use TestCollection;

    private function getUrls(): UrlResult
    {
        return (new ProductUrlProvider(
            $this->_getMock(ProductUrlProvider::class),
            $this->_getMock(Connection::class),
            $this->_getMock(RouterInterface::class),
            $this->_getMock(ProductFetcher::class),
        ))->getUrls($this->_createDefaultSalesChannelContext(), 10, 0);
    }

    public function test_product_url_provider(): void
    {
        $this->_createMock(ProductFetcher::class)
            ->method('getProducts')
            ->willReturn([
                [
                    'auto_increment' => '10',
                    'id' => Uuid::fromStringToHex('product-id-1'),
                    'created_at' => '2021-01-01 00:00:00',
                    'updated_at' => '2021-01-01 00:00:00',
                    'product_number' => 'SW-1000',
                    'sitemapPriority'  => 0.5
                ],[
                    'auto_increment' => '11',
                    'id' => Uuid::fromStringToHex('product-id-2'),
                    'created_at' => '2021-01-01 00:00:00',
                    'updated_at' => '2021-01-01 00:00:00',
                    'product_number' => 'SW-1001',
                    'sitemapPriority'  => 0.9
                ]
            ]);

        $this->_createMock(Connection::class)
            ->method('fetchAllAssociative')
            ->willReturn([
                [
                    'foreign_key' => Uuid::fromStringToHex('product-id-1'),
                    'seo_path_info' => 'product/sw-1000'
                ],[
                    'foreign_key' => Uuid::fromStringToHex('product-id-2'),
                    'seo_path_info' => 'product/sw-1001'
                ]
            ]);

        $this->_createMock(RouterInterface::class)
            ->expects($this->never())
            ->method('generate');

        $urlResult = $this->getUrls();

        self::assertCount(2, $urlResult->getUrls());

        self::assertSame('product/sw-1000', $urlResult->getUrls()[0]->getLoc());
        self::assertSame(0.5, $urlResult->getUrls()[0]->getPriority());
        self::assertSame('hourly', $urlResult->getUrls()[0]->getChangefreq());

        self::assertSame('product/sw-1001', $urlResult->getUrls()[1]->getLoc());
        self::assertSame(0.9, $urlResult->getUrls()[1]->getPriority());
        self::assertSame('hourly', $urlResult->getUrls()[1]->getChangefreq());
    }
}
