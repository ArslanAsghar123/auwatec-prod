<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\SocialMedia;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Seo\LiveTemplate\LiveTemplateConverter;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\CategorySeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\LandingpageSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;

class SocialMediaFetcher
{
    public function __construct(
        private readonly ProductSeoDataFetcher $productSeoDataFetcher,
        private readonly CategorySeoDataFetcher $categorySeoDataFetcher,
        private readonly LandingpageSeoDataFetcher $landingpageSeoDataFetcher,
        private readonly LiveTemplateConverter $liveTemplateConverter
    ) { }

    public function fetch(SocialMediaFetcherStruct $socialMediaFetcherStruct): ?SeoDataFetchResultStruct
    {
        $seoDataFetchResult = null;

        if (ProductDefinition::ENTITY_NAME === $socialMediaFetcherStruct->getEntityName()) {
            $seoDataFetchResult = $this->fetchProductSocialMedia($socialMediaFetcherStruct);

            if ($socialMediaFetcherStruct->getEntity() instanceof SalesChannelProductEntity && $socialMediaFetcherStruct->getSalesChannelContext()) {
                $this->liveTemplateConverter->translateSeoDataFetchResultProductPrice(
                    $seoDataFetchResult,
                    $socialMediaFetcherStruct->getEntity(),
                    $socialMediaFetcherStruct->getSalesChannelContext()
                );
            }
        }

        if (CategoryDefinition::ENTITY_NAME === $socialMediaFetcherStruct->getEntityName()) {
            $seoDataFetchResult = $this->fetchCategorySocialMedia($socialMediaFetcherStruct);
        }

        if (LandingPageDefinition::ENTITY_NAME === $socialMediaFetcherStruct->getEntityName()) {
            $seoDataFetchResult = $this->fetchLandingpageSocialMedia($socialMediaFetcherStruct);
        }

        if ($seoDataFetchResult && $socialMediaFetcherStruct->getSalesChannelContext()) {
            $this->liveTemplateConverter->translateSeoDataFetchResultShopName(
                $seoDataFetchResult,
                $socialMediaFetcherStruct->getSalesChannelContext()
            );
        }

        return $seoDataFetchResult;
    }

    /**
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    private function fetchProductSocialMedia(SocialMediaFetcherStruct $socialMediaFetcherStruct): SeoDataFetchResultStruct
    {
        return $this->productSeoDataFetcher->fetch(
            $socialMediaFetcherStruct->getEntityId(),
            $socialMediaFetcherStruct->getLanguageId(),
            null,
            true
        );
    }

    /**
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    private function fetchCategorySocialMedia(SocialMediaFetcherStruct $socialMediaFetcherStruct): SeoDataFetchResultStruct
    {
        return $this->categorySeoDataFetcher->fetch(
            $socialMediaFetcherStruct->getEntityId(),
            $socialMediaFetcherStruct->getLanguageId(),
            null
        );
    }

    /**
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    private function fetchLandingpageSocialMedia(SocialMediaFetcherStruct $socialMediaFetcherStruct): SeoDataFetchResultStruct
    {
        return $this->landingpageSeoDataFetcher->fetch(
            $socialMediaFetcherStruct->getEntityId(),
            $socialMediaFetcherStruct->getLanguageId(),
            null
        );
    }
}
