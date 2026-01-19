<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataFetcher;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\SeoUrl\SeoUrlRepository;
use DreiscSeoPro\Core\Foundation\Context\ContextFactory;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Common\DataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class CategorySeoDataFetcher
{
    public function __construct(
        private readonly ContextFactory $contextFactory,
        private readonly CategoryRepository $categoryRepository,
        private readonly LanguageChainFactory $languageChainFactory,
        private readonly SeoUrlRepository $seoUrlRepository,
        private readonly DataFetcher $dataFetcher
    ) { }

    public function fetchList(array $categoryIds, string $languageId, ?string $salesChannelId): array
    {
        $seoDataFetchResultStructs = [];
        $categoryEntities = $this->dataFetcher->fetchEntityCollection(
            $this->categoryRepository,
            $categoryIds,
            $languageId,
            false
        );

        if(empty($categoryEntities)) {
            return $seoDataFetchResultStructs;
        }

        foreach($categoryEntities as $categoryEntity) {
            $seoDataFetchResultStructs[$categoryEntity->getId()] = $this->fetch($categoryEntity->getId(), $languageId, $salesChannelId, $categoryEntity);
        }

        return $seoDataFetchResultStructs;
    }

    /**
     * @throws DBALException
     */
    public function fetch(string $referenceId, string $languageId, ?string $salesChannelId, ?CategoryEntity $categoryEntity = null): ?SeoDataFetchResultStruct
    {
        if (null === $categoryEntity) {
            /** Load the category */
            /** @var CategoryEntity $categoryEntity */
            $categoryEntity = $this->dataFetcher->fetchEntity(
                $this->categoryRepository,
                $referenceId,
                $languageId,
                false
            );
        }

        /** Return NULL if entity is null */
        if (null === $categoryEntity) {
            return null;
        }

        /** Fetch the seo base information */
        $seoDataFetchResultStruct = $this->dataFetcher->fetchBaseInformation(
            $categoryEntity,
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
