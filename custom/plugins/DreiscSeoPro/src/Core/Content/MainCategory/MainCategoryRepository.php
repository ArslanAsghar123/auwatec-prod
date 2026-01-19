<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\MainCategory;

use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Content\SalesChannel\SalesChannelRepository;
use DreiscSeoPro\Core\Foundation\Context\ContextFactory;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Seo\MainCategory\MainCategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Stopwatch\Stopwatch;

/**
* @method MainCategoryEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method MainCategorySearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class MainCategoryRepository extends EntityRepository
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param SalesChannelRepository $salesChannelRepository
     * @param ContextFactory $contextFactory
     */
    public function __construct(\Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository, private readonly CategoryRepository $categoryRepository, ProductRepository $productRepository, private readonly SalesChannelRepository $salesChannelRepository, private readonly ContextFactory $contextFactory)
    {
        parent::__construct($repository);
        $this->productRepository = $productRepository;
    }

    /**
     * Fetches the main category of the given product.
     * Returns the first category of the given sales channel, if no main category is defined.
     *
     * @param ProductEntity $translatedProductEntity
     */
    public function getProductMainCategory(Entity $translatedProductEntity, string $salesChannelId, ?string $preferredCategoryId = null): ?CategoryEntity
    {
        /** We fetch sales channel info, because no main category is set for this combination */
        $salesChannelEntity = $this->salesChannelRepository->getCached($salesChannelId);
        $navigationCategoryId = $salesChannelEntity->getNavigationCategoryId();

        /** At first we check, if a main category is set for this product */
        if (null !== $translatedProductEntity->getMainCategories()) {
            foreach($translatedProductEntity->getMainCategories()->getElements() as $mainCategoryEntity) {
                $categoryEntity = $this->categoryRepository->get($mainCategoryEntity->getCategoryId());

                /** Abort, if the category is not in the breadcrumb. In this case the category is not part of the sales channel */
                if (!array_key_exists($navigationCategoryId, $categoryEntity->getPlainBreadcrumb())) {
                    continue;
                }

                return $categoryEntity;
            }
        }

        /** Abort, if the sales channel or the navigation category of the sales channel is empty */
        if (null === $salesChannelEntity || null === $salesChannelEntity->getNavigationCategoryId()) {
            return null;
        }

        /** We filter the product categories by the navigation category of the sales channel */
        $navigationCategoryId = $salesChannelEntity->getNavigationCategoryId();
        $filteredCategoryCollection = $translatedProductEntity->getCategories()->filter(
            static fn(CategoryEntity $categoryEntity) => array_key_exists($navigationCategoryId, $categoryEntity->getPlainBreadcrumb())
        );

        /** Abort, if there is no category for this sales channel */
        if (null === $filteredCategoryCollection->first()) {
            return null;
        }

        if (null !== $preferredCategoryId && $filteredCategoryCollection->has($preferredCategoryId)) {
            return $filteredCategoryCollection->get($preferredCategoryId);
        }

        /** Return the first category we found */
        return $filteredCategoryCollection->first();
    }
}

