<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Core\Routing;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEnum;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Test\TestCollection;
use PHPUnit\Framework\Attributes\TestWith;
use Shopware\Core\Framework\Struct\ArrayStruct;
use PHPUnit\Framework\TestCase;
use DreiscSeoPro\Core\Routing\RedirectFinder;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/** @see RedirectFinder */
class RedirectFinderTest extends TestCase
{
    use TestCollection;

    /**
     * @var RedirectFinder
     */
    private $redirectFinder;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    protected function setUp(): void
    {
        $this->redirectFinder = $this->getContainer()->get(RedirectFinder::class);
        $this->dreiscSeoRedirectRepository = $this->getContainer()->get(DreiscSeoRedirectRepository::class);

        $this->salesChannelContext = $this->_createDefaultSalesChannelContext();
    }

    public function test_url_to_url_default(): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $expectedSeoRedirectId = Uuid::randomHex();

        $this->dreiscSeoRedirectRepository->upsert([
            [
                'id' => $expectedSeoRedirectId,
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        $dreiscSeoRedirect = $this->redirectFinder->findBySourceUrl(
            'from-url',
            $domainId
        );

        self::assertNotNull($dreiscSeoRedirect);
        self::assertSame($expectedSeoRedirectId, $dreiscSeoRedirect->getId());
    }

    public function test_fallback_to_url_without_get_param(): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $expectedSeoRedirectId = Uuid::randomHex();

        $this->dreiscSeoRedirectRepository->upsert([
            [
                'id' => $expectedSeoRedirectId,
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        $dreiscSeoRedirect = $this->redirectFinder->findBySourceUrl(
            'from-url?foo=bar&baz=qux',
            $domainId
        );

        self::assertNotNull($dreiscSeoRedirect);
        self::assertSame($expectedSeoRedirectId, $dreiscSeoRedirect->getId());

        self::assertTrue($dreiscSeoRedirect->hasExtension(RedirectFinder::QUERY_PARAMS));

        /** @var ArrayStruct $params */
        $params = $dreiscSeoRedirect->getExtension(RedirectFinder::QUERY_PARAMS);
        self::assertSame('bar', $params->get('foo'));
        self::assertSame('qux', $params->get('baz'));
    }

    public function test_fallback_to_two_urls_without_get_param(): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $expectedSeoRedirectId = Uuid::randomHex();

        $this->dreiscSeoRedirectRepository->upsert([
            [
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        /** This redirect is possible, but has not the highest priority */
        $this->dreiscSeoRedirectRepository->upsert([
            [
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url?baz=qux',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        /** This redirect is not possible, because not=in is not in foo=bar&baz=qux */
        $this->dreiscSeoRedirectRepository->upsert([
            [
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url?not=in',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        /** This redirect is not possible, because foo=bar is in foo=bar&baz=qux but not "not=in" */
        $this->dreiscSeoRedirectRepository->upsert([
            [
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url?foo=bar&not=in',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        /** This is the expected one, because foo=bar is in foo=bar&baz=qux and this is the first param */
        $this->dreiscSeoRedirectRepository->upsert([
            [
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url?foo',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        /** This is the expected one, because foo=bar and baz=qux are in foo=bar&baz=qux */
        $this->dreiscSeoRedirectRepository->upsert([
            [
                'id' => $expectedSeoRedirectId,
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url?baz=qux&foo=bar',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        /** This is the expected one, because foo=bar and baz=qux are in foo=bar&baz=qux */
        $this->dreiscSeoRedirectRepository->upsert([
            [
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url?baz=qux&foo=baSr',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        /** foo=bar is in foo=bar&baz=qux and this is the first param, so it is possible */
        $this->dreiscSeoRedirectRepository->upsert([
            [
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url?foo=bar',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        $dreiscSeoRedirect = $this->redirectFinder->findBySourceUrl(
            'from-url?foo=bar&baz=qux',
            $domainId
        );

        self::assertNotNull($dreiscSeoRedirect);
        self::assertSame($expectedSeoRedirectId, $dreiscSeoRedirect->getId());

        self::assertTrue($dreiscSeoRedirect->hasExtension(RedirectFinder::QUERY_PARAMS));

        /** @var ArrayStruct $params */
        $params = $dreiscSeoRedirect->getExtension(RedirectFinder::QUERY_PARAMS);
        self::assertSame('bar', $params->get('foo'));
        self::assertSame('qux', $params->get('baz'));
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_bugfix_missing_path(bool $additionalRedirects): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $expectedSeoRedirectId = Uuid::randomHex();

        if ($additionalRedirects) {
            $this->dreiscSeoRedirectRepository->upsert([
                [
                    'active' => true,
                    'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                    'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                    'sourcePath' => '?invalid_1=',
                    'sourceSalesChannelDomainId' => $domainId,
                    'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                    'redirectPath' => 'to-url_invalid_1',
                    'redirectSalesChannelDomainId' => $domainId
                ]
            ]);
        }

        $this->dreiscSeoRedirectRepository->upsert([
            [
                'id' => $expectedSeoRedirectId,
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => '?srsltid=',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        if ($additionalRedirects) {
            $this->dreiscSeoRedirectRepository->upsert([
                [
                    'active' => true,
                    'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                    'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                    'sourcePath' => '?invalid_2=',
                    'sourceSalesChannelDomainId' => $domainId,
                    'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                    'redirectPath' => 'to-url_invalid_2',
                    'redirectSalesChannelDomainId' => $domainId
                ]
            ]);
        }

        $dreiscSeoRedirect = $this->redirectFinder->findBySourceUrl(
            '?srsltid=AfmBOooDk67xyzz_1_tSvrEuuoFxZC7OpP2AhLxmfeKQb6Y2K1LbjnxU',
            $domainId
        );

        self::assertNotNull($dreiscSeoRedirect);
        self::assertSame($expectedSeoRedirectId, $dreiscSeoRedirect->getId());

        self::assertTrue($dreiscSeoRedirect->hasExtension(RedirectFinder::QUERY_PARAMS));

        /** @var ArrayStruct $params */
        $params = $dreiscSeoRedirect->getExtension(RedirectFinder::QUERY_PARAMS);
        self::assertSame('AfmBOooDk67xyzz_1_tSvrEuuoFxZC7OpP2AhLxmfeKQb6Y2K1LbjnxU', $params->get('srsltid'));
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_bugfix_missing_path_no_match(bool $additionalRedirects): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $expectedSeoRedirectId = Uuid::randomHex();

        if ($additionalRedirects) {
            $this->dreiscSeoRedirectRepository->upsert([
                [
                    'active' => true,
                    'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                    'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                    'sourcePath' => '?invalid_1=',
                    'sourceSalesChannelDomainId' => $domainId,
                    'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                    'redirectPath' => 'to-url_invalid_1',
                    'redirectSalesChannelDomainId' => $domainId
                ]
            ]);
        }

        $this->dreiscSeoRedirectRepository->upsert([
            [
                'id' => $expectedSeoRedirectId,
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => '?srsltid=',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        if ($additionalRedirects) {
            $this->dreiscSeoRedirectRepository->upsert([
                [
                    'active' => true,
                    'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                    'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                    'sourcePath' => '?invalid_2=',
                    'sourceSalesChannelDomainId' => $domainId,
                    'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                    'redirectPath' => 'to-url_invalid_2',
                    'redirectSalesChannelDomainId' => $domainId
                ]
            ]);
        }

        $dreiscSeoRedirect = $this->redirectFinder->findBySourceUrl(
            '?no_match=abc',
            $domainId
        );

        self::assertNull($dreiscSeoRedirect);
    }

    public function test_bugfix_2025_03_19__01(): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $expectedSeoRedirectId = Uuid::randomHex();

        $this->dreiscSeoRedirectRepository->upsert([
            [
                'id' => $expectedSeoRedirectId,
                'active' => true,
                'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
                'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
                'sourcePath' => 'from-url/another/url/',
                'sourceSalesChannelDomainId' => $domainId,
                'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
                'redirectPath' => 'to-url',
                'redirectSalesChannelDomainId' => $domainId
            ]
        ]);

        $dreiscSeoRedirect = $this->redirectFinder->findBySourceUrl(
            'from-url?foo=bar&baz=qux',
            $domainId
        );

        self::assertNull($dreiscSeoRedirect);
    }
}
