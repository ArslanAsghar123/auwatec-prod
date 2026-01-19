<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RobotsTag;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\Category\CategoryEnum;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\RobotsTagStruct;
use DreiscSeoPro\Core\Foundation\Context\ContextFactory;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\CategorySeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\LandingpageSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;

class RobotsTagFetcher
{
    public function __construct(
        private readonly NoIndexParameterSearcher $noIndexParameterSearcher,
        private readonly ProductSeoDataFetcher $productSeoDataFetcher,
        private readonly CategorySeoDataFetcher $categorySeoDataFetcher,
        private readonly LandingpageSeoDataFetcher $landingpageSeoDataFetcher
    ) { }

    /**
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    public function fetch(RobotsTagFetcherStruct $robotsTagStruct): ?string
    {
        $robotsTagSettings = $robotsTagStruct->getCustomSetting()->getMetaTags()->getRobotsTag();

        /** Try to fetch an individual tag value */
        $robotsTag = $this->fetchEntityRobotsTag($robotsTagStruct);

        /** Validate the fetched robots tag */
        if (null !== $robotsTag && !in_array($robotsTag, RobotsTagStruct::VALID_ROBOTS_TAGS, true)) {
            $robotsTag = null;
        }

        /** Fallback to default robots tag, if no individual was set */
        if (null === $robotsTag) {
            $robotsTag = $this->fetchEntityDefaultRobotsTag($robotsTagStruct);
        }

        /** Check for noindex parameters, if index robot tag is set */
        if (str_starts_with($robotsTag, 'index')) {
            if (true === $this->noIndexParameterSearcher->hasNoIndexParameter(
                $robotsTagSettings->getNoIndexRequestParameterConfig(),
                $robotsTagStruct->getRequestParams()
            )) {
                /** Replace index to noindex */
                $robotsTag = sprintf(
                    'no%s',
                    $robotsTag
                );
            }
        }

        return $robotsTag;
    }

    /**
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    private function fetchEntityRobotsTag(RobotsTagFetcherStruct $robotsTagStruct): ?string
    {
        if (ProductDefinition::ENTITY_NAME === $robotsTagStruct->getEntityName()) {
            return $this->fetchProductRobotsTag($robotsTagStruct);
        }

        if (CategoryDefinition::ENTITY_NAME === $robotsTagStruct->getEntityName()) {
            return $this->fetchCategoryRobotsTag($robotsTagStruct);
        }

        if (LandingPageDefinition::ENTITY_NAME === $robotsTagStruct->getEntityName()) {
            return $this->fetchLandingpageRobotsTag($robotsTagStruct);
        }

        return null;
    }

    private function fetchEntityDefaultRobotsTag(RobotsTagFetcherStruct $robotsTagStruct): ?string
    {
        $robotsSettings = $robotsTagStruct->getCustomSetting()->getMetaTags()->getRobotsTag();
        $robotsTag = null;

        if (ProductDefinition::ENTITY_NAME === $robotsTagStruct->getEntityName()) {
            $robotsTag = $robotsSettings->getDefaultRobotsTagProduct();
        }

        if (CategoryDefinition::ENTITY_NAME === $robotsTagStruct->getEntityName()) {
            $robotsTag = $robotsSettings->getDefaultRobotsTagCategory();
        }

        if(!empty($robotsTag)) {
            return $robotsTag;
        }

        return RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW;
    }

    /**
     * @return mixed|string|null
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    private function fetchProductRobotsTag(RobotsTagFetcherStruct $robotsTagStruct)
    {
        $seoDataFetchResultStruct = $this->productSeoDataFetcher->fetch(
            $robotsTagStruct->getEntityId(),
            $robotsTagStruct->getLanguageId(),
            null,
            true
        );

        if (true === $seoDataFetchResultStruct->isInheritedRobotsTag() || empty($seoDataFetchResultStruct->getRobotsTag())) {
            return null;
        }

        return $seoDataFetchResultStruct->getRobotsTag();
    }

    /**
     * @return mixed|null
     * @throws InconsistentCriteriaIdsException
     * @throws DBALException
     * @throws InvalidUuidException
     */
    private function fetchCategoryRobotsTag(RobotsTagFetcherStruct $robotsTagStruct)
    {
        $seoDataFetchResultStruct = $this->categorySeoDataFetcher->fetch(
            $robotsTagStruct->getEntityId(),
            $robotsTagStruct->getLanguageId(),
            null
        );

        if (true === $seoDataFetchResultStruct->isInheritedRobotsTag() || empty($seoDataFetchResultStruct->getRobotsTag())) {
            return null;
        }

        return $seoDataFetchResultStruct->getRobotsTag();
    }

    /**
     * @return mixed|null
     * @throws InconsistentCriteriaIdsException
     * @throws DBALException
     * @throws InvalidUuidException
     */
    private function fetchLandingpageRobotsTag(RobotsTagFetcherStruct $robotsTagStruct)
    {
        $seoDataFetchResultStruct = $this->landingpageSeoDataFetcher->fetch(
            $robotsTagStruct->getEntityId(),
            $robotsTagStruct->getLanguageId(),
            null
        );

        if (true === $seoDataFetchResultStruct->isInheritedRobotsTag() || empty($seoDataFetchResultStruct->getRobotsTag())) {
            return null;
        }

        return $seoDataFetchResultStruct->getRobotsTag();
    }
}
