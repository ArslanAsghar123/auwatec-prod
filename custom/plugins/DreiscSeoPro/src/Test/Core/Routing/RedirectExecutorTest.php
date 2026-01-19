<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Core\Routing;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEnum;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Core\Routing\RedirectFinder;
use DreiscSeoPro\Test\TestCollection;
use PHPUnit\Framework\TestCase;
use DreiscSeoPro\Core\Routing\RedirectExecutor;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/** @see RedirectExecutor */
class RedirectExecutorTest extends TestCase
{
    use TestCollection;

    /**
     * @var RedirectExecutor
     */
    private $redirectExecutor;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    protected function setUp(): void
    {
        $this->redirectExecutor = $this->getContainer()->get(RedirectExecutor::class);
        $this->dreiscSeoRedirectRepository = $this->getContainer()->get(DreiscSeoRedirectRepository::class);
        $this->salesChannelContext = $this->_createDefaultSalesChannelContext();
    }

    /**
     * @return Entity|null
     */
    private function createTestRedirect(\Closure $seoRedirectCallback = null): ?DreiscSeoRedirectEntity
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();

        $redirect = [
            'id' => Uuid::fromStringToHex('redirect'),
            'active' => true,
            'redirectHttpStatusCode' => DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301,
            'sourceType' => DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
            'sourcePath' => 'from-url',
            'sourceSalesChannelDomainId' => $domainId,
            'redirectType' => DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
            'redirectPath' => 'to-url',
            'redirectSalesChannelDomainId' => $domainId
        ];

        if ($seoRedirectCallback) {
            $seoRedirectCallback($redirect);
        }

        $this->dreiscSeoRedirectRepository->upsert([
            $redirect
        ]);

        return $this->dreiscSeoRedirectRepository->get(Uuid::fromStringToHex('redirect'));
    }

    public function test_getRedirect_url_default(): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $dreiscSeoRedirect = $this->createTestRedirect();

        $dreiscSeoRedirect->addExtension(
            RedirectFinder::QUERY_PARAMS,
            new ArrayStruct([
                'foo' => 'bar',
                'baz' => 'qux'
            ])
        );

        $url = $this->redirectExecutor->getRedirect($dreiscSeoRedirect, $domainId);

        self::assertSame('default.headless0/to-url', $url);
    }

    public function test_getRedirect_productEntity_default(): void
    {
        $product = $this->_createProduct();

        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $dreiscSeoRedirect = $this->createTestRedirect(function (&$redirect) use ($product) {
            $redirect['redirectType'] = DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT;
            $redirect['redirectProductId'] = $product->getId();

            return $redirect;
        });

        $dreiscSeoRedirect->addExtension(
            RedirectFinder::QUERY_PARAMS,
            new ArrayStruct([
                'foo' => 'bar',
                'baz' => 'qux'
            ])
        );

        $url = $this->redirectExecutor->getRedirect($dreiscSeoRedirect, $domainId);

        self::assertSame('default.headless0/detail/' . $product->getId(), $url);
    }

    public function test_getRedirect_url_activeParameterForwarding(): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $dreiscSeoRedirect = $this->createTestRedirect(fn(&$redirect) => $redirect['parameterForwarding'] = true);
        $dreiscSeoRedirect->addExtension(
            RedirectFinder::QUERY_PARAMS,
            new ArrayStruct([
                'foo' => 'bar',
                'baz' => 'qux'
            ])
        );

        $url = $this->redirectExecutor->getRedirect($dreiscSeoRedirect, $domainId);

        self::assertSame('default.headless0/to-url?foo=bar&baz=qux', $url);
    }

    public function test_getRedirect_url_activeParameterForwarding_paramOverlap(): void
    {
        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $dreiscSeoRedirect = $this->createTestRedirect(function (&$redirect) {
            $redirect['redirectPath'] = 'to-url?baz=qux';
            $redirect['parameterForwarding'] = true;
        });

        $dreiscSeoRedirect->addExtension(
            RedirectFinder::QUERY_PARAMS,
            new ArrayStruct([
                'foo' => 'bar',
                'baz' => 'qux'
            ])
        );

        $url = $this->redirectExecutor->getRedirect($dreiscSeoRedirect, $domainId);

        self::assertSame('default.headless0/to-url?baz=qux&foo=bar', $url);
    }

    public function test_getRedirect_productEntity_activeParameterForwarding(): void
    {
        $product = $this->_createProduct();

        $domainId = $this->salesChannelContext->getSalesChannel()->getDomains()->first()->getId();
        $dreiscSeoRedirect = $this->createTestRedirect(function (&$redirect) use ($product) {
            $redirect['redirectType'] = DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT;
            $redirect['redirectProductId'] = $product->getId();
            $redirect['parameterForwarding'] = true;

            return $redirect;
        });

        $dreiscSeoRedirect->addExtension(
            RedirectFinder::QUERY_PARAMS,
            new ArrayStruct([
                'foo' => 'bar',
                'baz' => 'qux'
            ])
        );

        $url = $this->redirectExecutor->getRedirect($dreiscSeoRedirect, $domainId);

        self::assertSame('default.headless0/detail/' . $product->getId() . '?foo=bar&baz=qux', $url);
    }
}
