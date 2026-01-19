<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Core\Routing\Product;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEnum;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Routing\RedirectExecutor;
use DreiscSeoPro\Core\Routing\RedirectFinder;
use DreiscSeoPro\Core\Routing\SourceSalesChannelDomainRestrictionChecker;
use DreiscSeoPro\Test\TestCollection;
use PHPUnit\Framework\TestCase;
use DreiscSeoPro\Core\Routing\Product\ProductRedirectSearcher;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class ProductRedirectSearcherTest extends TestCase
{
    use TestCollection;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    protected function setUp(): void
    {
        $this->dreiscSeoRedirectRepository = $this->getContainer()->get(DreiscSeoRedirectRepository::class);
        $this->salesChannelContext = $this->_createDefaultSalesChannelContext();
    }

    protected function createProductRedirectSearcherMock(): ProductRedirectSearcher
    {
        return new ProductRedirectSearcher(
            $this->_getMock(DreiscSeoRedirectRepository::class),
            $this->_getMock(RedirectExecutor::class),
            $this->_getMock(SourceSalesChannelDomainRestrictionChecker::class),
            $this->_getMock(ProductRepository::class)
        );
    }

    public function test_queryParameters(): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $productEntity = $this->_createProduct();

        $this->dreiscSeoRedirectRepository->upsert([
            [
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT,
                'sourceProductId' => $productEntity->getId(),
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        $request = new Request(
            [], [],
            [
                SalesChannelRequest::ATTRIBUTE_DOMAIN_ID => $domainId
            ],
            [], [],
            [
                'QUERY_STRING' => 'foo=bar&baz=qux'
            ],
        );

        $this->_createMock(RedirectExecutor::class)
            ->expects($this->once())
            ->method('redirect')
            ->willReturnCallback(
            function (DreiscSeoRedirectEntity $dreiscSeoRedirectEntity, $salesChannelDomainId) use ($productEntity, $domainId) {
                self::assertSame($productEntity->getId(), $dreiscSeoRedirectEntity->getSourceProductId());
                self::assertSame($domainId, $salesChannelDomainId);
                self::assertTrue($dreiscSeoRedirectEntity->hasExtension(RedirectFinder::QUERY_PARAMS));

                /** @var ArrayStruct $queryParams */
                $queryParams = $dreiscSeoRedirectEntity->getExtension(RedirectFinder::QUERY_PARAMS);
                self::assertEquals([
                    'foo' => 'bar',
                    'baz' => 'qux'
                ], $queryParams->all());
            }
        );

        $this->createProductRedirectSearcherMock()->search(
            $request,
            $productEntity->getId()
        );
    }
}
