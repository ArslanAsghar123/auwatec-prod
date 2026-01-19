<?php declare(strict_types=1);

namespace DreiscSeoPro\Decorator\Core\Content\Sitemap\Provider;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Sitemap\LandigpageFetcher;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Sitemap\Provider\AbstractUrlProvider;
use Shopware\Core\Content\Sitemap\Service\ConfigHandler;
use Shopware\Core\Content\Sitemap\Struct\Url;
use Shopware\Core\Content\Sitemap\Struct\UrlResult;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\RouterInterface;

class LandingpageUrlProvider extends AbstractUrlProvider
{
    final public const CHANGE_FREQ = 'daily';

    public function __construct(
        private readonly LanguageChainFactory $languageChainFactory,
        private readonly Connection $connection,
        private readonly IteratorFactory $iteratorFactory,
        private readonly RouterInterface $router,
        private readonly LandingPageDefinition $landingPageDefinition,
        private readonly LandigpageFetcher $landingPageFetcher
    ) { }

    public function getDecorated(): AbstractUrlProvider
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return 'dreisc_seo_landingpage';
    }

    public function getUrls(SalesChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        $landingPages = $this->landingPageFetcher->getLandingpages($context, $limit, $offset);

        if (empty($landingPages)) {
            return new UrlResult(
                [],
                $this->landingPageFetcher->getNextAvailableOffset($context, $limit, $offset)
            );
        }
        $keys = FetchModeHelper::keyPair($landingPages);

        $seoUrls = $this->getSeoUrls(array_values($keys), 'frontend.landing.page', $context, $this->connection);

        /** @var array<string, array{seo_path_info: string}> $seoUrls */
        $seoUrls = FetchModeHelper::groupUnique($seoUrls);

        $urls = [];
        $url = new Url();

        foreach ($landingPages as $indexKey => $landingPage) {
            $lastMod = $landingPage['updated_at'] ?: $landingPage['created_at'];

            $lastMod = (new \DateTime($lastMod))->format(Defaults::STORAGE_DATE_TIME_FORMAT);

            $newUrl = clone $url;

            if (isset($seoUrls[$landingPage['id']])) {
                $newUrl->setLoc($seoUrls[$landingPage['id']]['seo_path_info']);
            } else {
                continue;
            }

            $newUrl->setLastmod(new \DateTime($lastMod));
            $newUrl->setChangefreq(self::CHANGE_FREQ);
            $newUrl->setResource(CategoryEntity::class);
            $newUrl->setIdentifier($landingPage['id']);

            if (isset($landingPage['sitemapPriority'])) {
                $newUrl->setPriority($landingPage['sitemapPriority']);
            }

            $urls[] = $newUrl;
        }


        if (empty($urls)) {
            return new UrlResult([], null);
        }

        return new UrlResult($urls, $offset + $limit);
    }
}
