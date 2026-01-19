<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Category;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;

/**
* @method CategoryEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method CategorySearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class CategoryRepository extends EntityRepository
{
    /**
     * Check if the category id $searchInPathCategoryId is in the path
     * field of the category of the id $categoryId
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function hasInPath(string $categoryId, string $searchInPathCategoryId): bool
    {
        $criteria = (new Criteria())
            ->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_AND, [
                    new EqualsFilter('id', $categoryId),
                    new ContainsFilter('path', '|' . $searchInPathCategoryId . '|')
                ])
            );

        $categoryEntity = $this->search($criteria)->first();
        if (null === $categoryEntity) {
            return false;
        }

        return true;
    }

    /**
     * Search and returns the ids of the child-categories of the given category
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getChildIds(string $categoryId): IdSearchResult
    {
        return $this->searchIds((new Criteria())
            ->addFilter(
                new EqualsFilter('parentId', $categoryId)
            )
        );
    }
}

