<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataFetcher;

use DreiscSeoPro\Core\Content\LandingPage\LandingPageRepository;
use DreiscSeoPro\Core\Content\SeoUrl\SeoUrlRepository;
use DreiscSeoPro\Core\Foundation\Context\ContextFactory;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Common\DataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\LandingPage\LandingPageEntity;

class LandingpageSeoDataFetcher
{
    public function __construct(
        private readonly ContextFactory $contextFactory,
        private readonly LandingPageRepository $landingPageRepository,
        private readonly LanguageChainFactory $languageChainFactory,
        private readonly SeoUrlRepository $seoUrlRepository,
        private readonly DataFetcher $dataFetcher
    ) { }

    /**
     * @throws DBALException
     */
    public function fetch(string $referenceId, string $languageId, ?string $salesChannelId, ?LandingPageEntity $landingpageEntity = null): ?SeoDataFetchResultStruct
    {
        if (null === $landingpageEntity) {
            /** Load the category */
            /** @var LandingPageEntity $landingpageEntity */
            $landingpageEntity = $this->dataFetcher->fetchEntity(
                $this->landingPageRepository,
                $referenceId,
                $languageId,
                false
            );
        }

        /** Return NULL if entity is null */
        if (null === $landingpageEntity) {
            return null;
        }

        /** Fetch the seo base information */
        $seoDataFetchResultStruct = $this->dataFetcher->fetchBaseInformation(
            $landingpageEntity,
            $referenceId,
            $languageId,
            $salesChannelId,
            null,
            SeoUrlRepository::ROUTE_NAME__FRONTEND_NAVIGATION_PAGE
        );

        /** Return the values */
        return $seoDataFetchResultStruct;
    }
}
