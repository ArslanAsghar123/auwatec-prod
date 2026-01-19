<?php declare(strict_types=1);

namespace DreiscSeoPro\Decorator\Core\Content\Sitemap\Provider;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Sitemap\ProductFetcher;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Sitemap\Provider\AbstractUrlProvider;
use Shopware\Core\Content\Sitemap\Service\ConfigHandler;
use Shopware\Core\Content\Sitemap\Struct\Url;
use Shopware\Core\Content\Sitemap\Struct\UrlResult;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\RouterInterface;

class ProductUrlProvider extends AbstractUrlProvider
{
    final public const CHANGE_FREQ = 'hourly';

    public const CONFIG_HIDE_AFTER_CLOSEOUT = 'core.listing.hideCloseoutProductsWhenOutOfStock';

    public function __construct(
        private AbstractUrlProvider $decorated,
        private readonly Connection $connection,
        private readonly RouterInterface $router,
        private readonly ProductFetcher $productFetcher
    ) { }

    public function getDecorated(): AbstractUrlProvider
    {
        return $this->decorated;
    }

    public function getName(): string
    {
        return $this->decorated->getName();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function getUrls(SalesChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        $products = $this->productFetcher->getProducts($context, $limit, $offset);

        if (empty($products)) {
            return new UrlResult(
                [],
                $this->productFetcher->getNextAvailableOffset($context, $limit, $offset)
            );
        }

        $keys = FetchModeHelper::keyPair($products);

        $seoUrls = $this->getSeoUrls(array_values($keys), 'frontend.detail.page', $context, $this->connection);

        /** @var array<string, array{seo_path_info: string}> $seoUrls */
        $seoUrls = FetchModeHelper::groupUnique($seoUrls);

        $urls = [];
        $url = new Url();

        foreach ($products as $product) {
            $lastMod = $product['updated_at'] ?: $product['created_at'];

            $lastMod = (new \DateTime($lastMod))->format(Defaults::STORAGE_DATE_TIME_FORMAT);

            $newUrl = clone $url;

            if (isset($seoUrls[$product['id']])) {
                $newUrl->setLoc($seoUrls[$product['id']]['seo_path_info']);
            } else {
                $newUrl->setLoc($this->router->generate('frontend.detail.page', ['productId' => $product['id']]));
            }

            $newUrl->setLastmod(new \DateTime($lastMod));
            $newUrl->setChangefreq(self::CHANGE_FREQ);
            $newUrl->setResource(ProductEntity::class);
            $newUrl->setIdentifier($product['id']);

            /** CORE-CHANGES - START  */
            if (isset($product['sitemapPriority'])) {
                $newUrl->setPriority($product['sitemapPriority']);
            }
            /** CORE-CHANGES - END  */

            $urls[] = $newUrl;
        }

        $keys = array_keys($keys);
        /** @var int|null $nextOffset */
        $nextOffset = array_pop($keys);

        return new UrlResult($urls, $nextOffset);
    }
}
