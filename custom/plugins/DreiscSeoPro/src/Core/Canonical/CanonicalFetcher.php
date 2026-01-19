<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Canonical;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Seo\SeoUrlAssembler;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\CategorySeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\LandingpageSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Test\Core\Canonical\CanonicalFetcherTest;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;

/** @see CanonicalFetcherTest */
class CanonicalFetcher
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(
        private readonly ProductSeoDataFetcher $productSeoDataFetcher,
        private readonly CategorySeoDataFetcher $categorySeoDataFetcher,
        ProductRepository $productRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly SeoUrlAssembler $seoUrlAssembler,
        private readonly LandingpageSeoDataFetcher $landingpageSeoDataFetcher,
        private readonly CustomSettingLoader $customSettingLoader
    )
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    public function fetch(CanonicalFetcherStruct $canonicalFetcherStruct): ?string
    {
        /** Get the seo data fetch result */
        $seoDataFetchResultStruct = $this->getSeoDataFetchResultStruct($canonicalFetcherStruct);
        if (null === $seoDataFetchResultStruct) {
            return null;
        }
        
        if ($canonicalFetcherStruct->getEntity() instanceof SalesChannelProductEntity) {
            /** @var SalesChannelProductEntity $product */
            $product = $canonicalFetcherStruct->getEntity();
            $productCustomFields = $product->getCustomFields();

            /** Load the custom settings */
            $customSettings = $this->customSettingLoader->load($canonicalFetcherStruct->getSalesChannelId(), true);

            /** Parent canonical inheritance for variants */
            $this->productParentInheritance(
                $product,
                $canonicalFetcherStruct,
                $seoDataFetchResultStruct,
                $customSettings
            );
        }

        return match ($seoDataFetchResultStruct->getCanonicalLinkType()) {
            ProductEnum::CANONICAL_LINK_TYPE__EXTERNAL_URL => $this->progressExternalUrl($seoDataFetchResultStruct),
            ProductEnum::CANONICAL_LINK_TYPE__PRODUCT_URL => $this->progressProductUrl($seoDataFetchResultStruct, $canonicalFetcherStruct),
            ProductEnum::CANONICAL_LINK_TYPE__CATEGORY_URL => $this->progressCategoryUrl($seoDataFetchResultStruct, $canonicalFetcherStruct),
            default => null,
        };
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws DBALException
     * @throws InvalidUuidException
     */
    private function getSeoDataFetchResultStruct(CanonicalFetcherStruct $canonicalFetcherStruct): ?SeoDataFetchResultStruct
    {
        if (ProductDefinition::ENTITY_NAME === $canonicalFetcherStruct->getEntityName()) {
            return $this->productSeoDataFetcher->fetch(
                $canonicalFetcherStruct->getEntityId(),
                $canonicalFetcherStruct->getLanguageId(),
                $canonicalFetcherStruct->getSalesChannelId(),
                true
            );
        }

        elseif (CategoryDefinition::ENTITY_NAME === $canonicalFetcherStruct->getEntityName()) {
            return $this->categorySeoDataFetcher->fetch(
                $canonicalFetcherStruct->getEntityId(),
                $canonicalFetcherStruct->getLanguageId(),
                $canonicalFetcherStruct->getSalesChannelId()
            );
        }

        elseif (LandingPageDefinition::ENTITY_NAME === $canonicalFetcherStruct->getEntityName()) {
            return $this->landingpageSeoDataFetcher->fetch(
                $canonicalFetcherStruct->getEntityId(),
                $canonicalFetcherStruct->getLanguageId(),
                $canonicalFetcherStruct->getSalesChannelId()
            );
        }

        return null;
    }

    private function progressExternalUrl(SeoDataFetchResultStruct $seoDataFetchResultStruct): ?string
    {
        $externalUrl = $seoDataFetchResultStruct->getCanonicalLinkReference();
        if(empty($externalUrl)) {
            return null;
        }

        return $externalUrl;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function progressProductUrl(SeoDataFetchResultStruct $seoDataFetchResultStruct, CanonicalFetcherStruct $canonicalFetcherStruct): ?string
    {
        $productId = $seoDataFetchResultStruct->getCanonicalLinkReference();
        if(empty($productId)) {
            return null;
        }

        /** Fetch the product entity */
        $productEntity = $this->productRepository->get($productId);

        return $this->progressEntityUrl($productEntity, $canonicalFetcherStruct);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function progressCategoryUrl(SeoDataFetchResultStruct $seoDataFetchResultStruct, CanonicalFetcherStruct $canonicalFetcherStruct): ?string
    {
        $categoryId = $seoDataFetchResultStruct->getCanonicalLinkReference();
        if(empty($categoryId)) {
            return null;
        }

        /** Fetch the category entity */
        $categoryEntity = $this->categoryRepository->get($categoryId);

        return $this->progressEntityUrl($categoryEntity, $canonicalFetcherStruct);
    }

    /**
     * @param Entity $entity
     * @throws InconsistentCriteriaIdsException
     */
    private function progressEntityUrl(?Entity $entity, CanonicalFetcherStruct $canonicalFetcherStruct): ?string
    {
        /** Abort, if category is not available */
        if (null === $entity) {
            return null;
        }

        /** Fetch the  */
        $urlInfo = $this->seoUrlAssembler->assemble(
            $entity,
            $canonicalFetcherStruct->getSalesChannelId(),
            $canonicalFetcherStruct->getLanguageId()
        );

        if (!empty($urlInfo)) {
            $salesChannelDomainId = $canonicalFetcherStruct->getSalesChannelDomainId();
            if (!empty($urlInfo['absolutePaths']) && !empty($urlInfo['absolutePaths'][$salesChannelDomainId])) {
                return $urlInfo['absolutePaths'][$salesChannelDomainId];
            }
        }

        return null;
    }

    /**
     * @param SalesChannelProductEntity $product
     * @param CanonicalFetcherStruct $canonicalFetcherStruct
     * @param SeoDataFetchResultStruct $seoDataFetchResultStruct
     * @return void
     */
    public function productParentInheritance(SalesChannelProductEntity $product, CanonicalFetcherStruct $canonicalFetcherStruct, SeoDataFetchResultStruct $seoDataFetchResultStruct, CustomSettingStruct $customSettings): void
    {
        if (!$customSettings->getCanonical()->getGeneral()->getParentCanonicalInheritance() && empty($product->getCustomFields()['enable_parent_canonical_inheritance'])) {
            return;
        }
        
        if($customSettings->getCanonical()->getGeneral()->getParentCanonicalInheritance() && !empty($product->getCustomFields()['disable_parent_canonical_inheritance'])) {
            return;
        }

        if (false || null === $product->getParentId()) {
            return;
        }

        $canonicalFetcherStruct->setEntityId($product->getParentId());
        $canonicalFetcherStruct->setEntity(null);

        /** Fetch the seo data for the parent */
        $parentSeoDataFetchResultStruct = $this->getSeoDataFetchResultStruct($canonicalFetcherStruct);
        if (null === $parentSeoDataFetchResultStruct) {
            return;
        }

        /** Assign the parent seo data to the current seo data */
        $seoDataFetchResultStruct->assign($parentSeoDataFetchResultStruct->toArray());

        /** Abort if a valid canonical link type is stored for the parent product */
        if (in_array($seoDataFetchResultStruct->getCanonicalLinkType(), ProductEnum::VALID_CANONICAL_LINK_TYPES)) {
            return;
        }

        /** Otherwise we have to explicitly load the URL of the parent product */
        $seoDataFetchResultStruct->setCanonicalLinkType(ProductEnum::CANONICAL_LINK_TYPE__PRODUCT_URL);
        $seoDataFetchResultStruct->setCanonicalLinkReference($product->getParentId());
    }
}
