<?php declare(strict_types=1);

namespace DreiscSeoPro\Decorator\Core\Content\Sitemap\Provider;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Sitemap\CategoryFetcher;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Sitemap\Provider\AbstractUrlProvider;
use Shopware\Core\Content\Sitemap\Service\ConfigHandler;
use Shopware\Core\Content\Sitemap\Struct\Url;
use Shopware\Core\Content\Sitemap\Struct\UrlResult;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\RouterInterface;

class CategoryUrlProvider extends AbstractUrlProvider
{
    final public const CHANGE_FREQ = 'daily';

    public function __construct(
        private readonly AbstractUrlProvider $decorated,
        private readonly LanguageChainFactory $languageChainFactory,

        private readonly ConfigHandler $configHandler,
        private readonly Connection $connection,
        private readonly CategoryDefinition $definition,
        private readonly IteratorFactory $iteratorFactory,
        private readonly RouterInterface $router,
        private readonly CategoryFetcher $categoryFetcher
    ) { }

    public function getDecorated(): AbstractUrlProvider
    {
        return $this->decorated;
    }

    public function getName(): string
    {
        return $this->decorated->getName();
    }

    public function getUrls(SalesChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        $categories = $this->categoryFetcher->getCategories($context, $limit, $offset);

        if (empty($categories)) {
            return new UrlResult(
                [],
                $this->categoryFetcher->getNextAvailableOffset($context, $limit, $offset)
            );
        }
        $keys = FetchModeHelper::keyPair($categories);

        $seoUrls = $this->getSeoUrls(array_values($keys), 'frontend.navigation.page', $context, $this->connection);

        /** @var array<string, array{seo_path_info: string}> $seoUrls */
        $seoUrls = FetchModeHelper::groupUnique($seoUrls);

        $urls = [];
        $url = new Url();

        foreach ($categories as $category) {
            $lastMod = $category['updated_at'] ?: $category['created_at'];

            $lastMod = (new \DateTime($lastMod))->format(Defaults::STORAGE_DATE_TIME_FORMAT);

            $newUrl = clone $url;

            if (isset($seoUrls[$category['id']])) {
                $newUrl->setLoc($seoUrls[$category['id']]['seo_path_info']);
            } else {
                $newUrl->setLoc($this->router->generate('frontend.navigation.page', ['navigationId' => $category['id']]));
            }

            $newUrl->setLastmod(new \DateTime($lastMod));
            $newUrl->setChangefreq(self::CHANGE_FREQ);
            $newUrl->setResource(CategoryEntity::class);
            $newUrl->setIdentifier($category['id']);

            /** CORE-CHANGES - START  */
            if (isset($category['sitemapPriority'])) {
                $newUrl->setPriority($category['sitemapPriority']);
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
