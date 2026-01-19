<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataFetcher;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Content\SeoUrl\SeoUrlRepository;
use DreiscSeoPro\Core\Foundation\Context\ContextFactory;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Common\DataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Symfony\Component\Stopwatch\Stopwatch;

class ProductSeoDataFetcher
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(
        private readonly ContextFactory $contextFactory,
        private readonly CategoryRepository $categoryRepository,
        private readonly LanguageChainFactory $languageChainFactory,
        private readonly SeoUrlRepository $seoUrlRepository,
        ProductRepository $productRepository,
        private readonly DataFetcher $dataFetcher)
    {
        $this->productRepository = $productRepository;
    }

    public function fetchList(array $productIds, string $languageId, ?string $salesChannelId, bool $considerInheritance = false): array
    {
        $seoDataFetchResultStructs = [];
        $productEntities = $this->dataFetcher->fetchEntityCollection(
            $this->productRepository,
            $productIds,
            $languageId,
            $considerInheritance
        );

        if(empty($productEntities)) {
            return $seoDataFetchResultStructs;
        }

        foreach($productEntities as $productEntity) {
            $seoDataFetchResultStructs[$productEntity->getId()] = $this->fetch($productEntity->getId(), $languageId, $salesChannelId, $considerInheritance, $productEntity);
        }

        return $seoDataFetchResultStructs;
    }

    /**
     * @return SeoDataFetchResultStruct
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     * @throws InvalidUuidException
     */
    public function fetch(string $referenceId, string $languageId, ?string $salesChannelId, bool $considerInheritance = false, ?Entity $productEntity = null): ?SeoDataFetchResultStruct
    {
        if (null === $productEntity) {
            /** Load the product */
            $productEntity = $this->dataFetcher->fetchEntity(
                $this->productRepository,
                $referenceId,
                $languageId,
                $considerInheritance
            );
        }

        /** Return NULL if entity is null */
        if (null === $productEntity) {
            return null;
        }

        /** Fetch parent custom fields */
        $parentSeoDataFetchResultStruct = null;
        if (true === $considerInheritance && null !== $productEntity->getParentId()) {
            /** This is necessary because the consider inheritance flag does not work for custom fields */
            $parentSeoDataFetchResultStruct = $this->fetch(
                $productEntity->getParentId(),
                $languageId,
                $salesChannelId,
                false
            );
        }

        /** Fetch the seo base information */
        $seoDataFetchResultStruct = $this->dataFetcher->fetchBaseInformation(
            $productEntity,
            $referenceId,
            $languageId,
            $salesChannelId,
            $parentSeoDataFetchResultStruct,
            SeoUrlRepository::ROUTE_NAME__FRONTEND_DETAIL_PAGE
        );

        /** Return the values */
        return $seoDataFetchResultStruct;
    }
}
