<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk;

use DreiscSeoPro\Core\Cache\MessageBusCacheInvalidator;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Seo\MainCategory\MainCategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

/**
* @method DreiscSeoBulkEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method DreiscSeoBulkSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class DreiscSeoBulkRepository extends EntityRepository
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var EntitySearchResult|null
     */
    static public $cachedSeoBulks = null;

    static private int $cachedSeoBulksTimestamp = 0;

    /**
     * @var EntitySearchResult|null
     */
    static public $cachedCategories = null;

    /**
     * @var EntitySearchResult|null
     */
    static public $cachedCategoryParentIds = null;

    static private int $cachedCategoryTimestamp = 0;

    /**
     * @param \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository,
        private readonly CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        private readonly MessageBusCacheInvalidator $messageBusCacheInvalidator
    )
    {
        parent::__construct($repository);
        $this->productRepository = $productRepository;
    }

    public function resetCache()
    {
        self::$cachedSeoBulks = null;
        self::$cachedCategories = null;
        self::$cachedCategoryParentIds = null;
    }

    /**
     * @param array $data
     * @param Context|null $context
     * @return EntityWrittenContainerEvent
     */
    public function create(array $data, ?Context $context = null): EntityWrittenContainerEvent
    {
        /** Reset the cached seo bulks */
        $this->resetCache();

        return parent::create($data, $context);
    }

    /**
     * @param array $data
     * @param Context|null $context
     * @return EntityWrittenContainerEvent
     */
    public function update(array $data, ?Context $context = null): EntityWrittenContainerEvent
    {
        /** Reset the cached seo bulks */
        $this->resetCache();

        return parent::update($data, $context);
    }

    /**
     * @param array $updateData
     * @param Criteria $criteria
     * @param Context|null $context
     * @return EntityWrittenContainerEvent
     */
    public function updateByCriteria(array $updateData, Criteria $criteria, ?Context $context = null): EntityWrittenContainerEvent
    {
        /** Reset the cached seo bulks */
        $this->resetCache();

        return parent::updateByCriteria($updateData, $criteria, $context);
    }

    /**
     * @param array $data
     * @param Context|null $context
     * @return EntityWrittenContainerEvent
     */
    public function upsert(array $data, ?Context $context = null): EntityWrittenContainerEvent
    {
        /** Reset the cached seo bulks */
        $this->resetCache();

        return parent::upsert($data, $context);
    }

    /**
     * @param array $data
     * @param Context|null $context
     * @return EntityWrittenContainerEvent
     */
    public function delete(array $data, ?Context $context = null): EntityWrittenContainerEvent
    {
        /** Reset the cached seo bulks */
        $this->resetCache();

        return parent::delete($data, $context);
    }

    /**
     * @param string $entityId
     * @param Context|null $context
     * @return EntityWrittenContainerEvent
     */
    public function deleteById(string $entityId, ?Context $context = null): EntityWrittenContainerEvent
    {
        /** Reset the cached seo bulks */
        $this->resetCache();

        return parent::deleteById($entityId, $context);
    }

    /**
     * @param Criteria $criteria
     * @param Context|null $context
     * @return EntityWrittenContainerEvent|null
     */
    public function deleteByCriteria(Criteria $criteria, ?Context $context = null): ?EntityWrittenContainerEvent
    {
        /** Reset the cached seo bulks */
        $this->resetCache();

        return parent::deleteByCriteria($criteria, $context);
    }

    /**
     * Fetches the responsible seo bulk configuration for the given setting.
     *
     * This means that it will return the seo bulk of the current category,
     * a category which inherits its configuration or null.
     *
     * @param string $categoryId
     * @param string $area
     * @param string $seoOption
     * @param string $languageId
     * @param string|null $salesChannelId
     * @throws InconsistentCriteriaIdsException
     */
    public function getResponsibleSeoBulk(string $categoryId, string $area, string $seoOption, string $languageId, string $salesChannelId = null): ?DreiscSeoBulkEntity
    {
        /** Load the seo bulk, if available */
        /** Return the current seo bulk configuration, if its not null and a template is defined */
        $dreiscSeoBulkEntity = $this->getCachedSeoBulk($categoryId, $area, $seoOption, $languageId, $salesChannelId);

        if (null !== $dreiscSeoBulkEntity && null !== $dreiscSeoBulkEntity->getDreiscSeoBulkTemplateId()) {
            return $dreiscSeoBulkEntity;
        }

        /** There is no seo bulk setting for the given category, so we look for a parent category which inherits a template */
        return $this->getResponsibleParentSeoBulk($categoryId, $area, $seoOption, $languageId, $salesChannelId);
    }

    /**
     * Fetches the responsible seo bulk configuration for the given product id respecting the bulk priority.
     *
     * @param string|null $salesChannelId
     * @param ProductCollection|null $productCollection
     */
    public function getResponsibleProductSeoBulkRespectPriority(string $productId, string $seoOption, string $languageId, string $salesChannelId = null, ?EntityCollection $productCollection = null): ?DreiscSeoBulkEntity
    {
        $productEntity = $this->fetchProductEntityByCollection($productId, $productCollection);
        if (null === $productEntity) {
            /** Alternate way */
            /** Create a context which active inheritance */
            $context = Context::createDefaultContext();
            $context->setConsiderInheritance(true);

            /** Fetch assigned categories of the product */
            $productEntity = $this->productRepository->get($productId, [ 'categories', 'mainCategories' ], $context);
        }

		/** Abort, if $productEntity is null */
		if(null === $productEntity) {
			return null;
		}

        $assignedCategories = $productEntity->getCategories();

        /** Abort, if no category was found */
        if (null === $assignedCategories || null === $assignedCategories->first()) {
            return null;
        }

        /** Fetch all possible bulk entities */
        $possibleDreiscSeoBulkEntities = [];

        /** First we look after the main category, if set */
        if (null !== $salesChannelId && null !== $productEntity->getMainCategories()) {
            /** @var MainCategoryEntity $mainCategory */
            $mainCategory = $productEntity->getMainCategories()->filterBySalesChannelId($salesChannelId)->first();
            if (null !== $mainCategory) {
                $tmpDreiscSeoBulkEntity = $this->getResponsibleSeoBulk(
                    $mainCategory->getCategoryId(),
                    DreiscSeoBulkEnum::AREA__PRODUCT,
                    $seoOption,
                    $languageId,
                    $salesChannelId
                );

                if (null !== $tmpDreiscSeoBulkEntity) {
                    $possibleDreiscSeoBulkEntities[] = $tmpDreiscSeoBulkEntity;
                }
            }
        }

        foreach($assignedCategories as $assignedCategory) {
            $tmpDreiscSeoBulkEntity = $this->getResponsibleSeoBulk(
                $assignedCategory->getId(),
                DreiscSeoBulkEnum::AREA__PRODUCT,
                $seoOption,
                $languageId,
                $salesChannelId
            );

            if (null !== $tmpDreiscSeoBulkEntity) {
                $possibleDreiscSeoBulkEntities[] = $tmpDreiscSeoBulkEntity;
            }
        }

        /** Abort, if no bulk entity was found */
        if(empty($possibleDreiscSeoBulkEntities)) {
            return null;
        }

        /** Sort by priority if more then one is possible */
        if (count($possibleDreiscSeoBulkEntities) > 1) {
            /** Sort by priority */
            usort($possibleDreiscSeoBulkEntities, static fn(DreiscSeoBulkEntity $dreiscSeoBulkEntityA, DreiscSeoBulkEntity $dreiscSeoBulkEntityB) => (int) $dreiscSeoBulkEntityA->getPriority() < (int) $dreiscSeoBulkEntityB->getPriority());

            /** @var DreiscSeoBulkEntity $dreiscSeoBulkEntity */
            $dreiscSeoBulkEntity = current($possibleDreiscSeoBulkEntities);
        } else {
            /** Otherwise select the first */
            /** @var DreiscSeoBulkEntity $dreiscSeoBulkEntity */
            $dreiscSeoBulkEntity = current($possibleDreiscSeoBulkEntities);
        }

        /** Abort, if there is no template available for this category */
        if(null === $dreiscSeoBulkEntity || null === $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()) {
            return null;
        }

        return $dreiscSeoBulkEntity;
    }

    /**
     * Fetches all seo bulk entries which use the seo bulk template with the given id
     *
     * @param $dreiscSeoBulkTemplateId
     * @param null $limit
     * @return DreiscSeoBulkSearchResult
     * @throws InconsistentCriteriaIdsException
     */
    public function getSeoBulkListBySeoBulkTemplateId($dreiscSeoBulkTemplateId, $limit = null): EntitySearchResult
    {
        $criteria = (new Criteria())
            ->addFilter(
                new EqualsFilter(
                    DreiscSeoBulkEntity::DREISC_SEO_BULK_TEMPLATE_ID__PROPERTY_NAME,
                    $dreiscSeoBulkTemplateId
                )
            )
            ->addAssociation(DreiscSeoBulkEntity::CATEGORY__PROPERTY_NAME)
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        if (null !== $limit) {
            $criteria->setLimit($limit);
        }

        return $this->search($criteria);
    }

    /**
     * Looks if there is a parent for the given config which inherits the seo bulk setting
     *
     * @param string|null $salesChannelId
     * @throws InconsistentCriteriaIdsException
     */
    protected function getResponsibleParentSeoBulk(string $categoryId, string $area, string $seoOption, string $languageId, string $salesChannelId = null): ?DreiscSeoBulkEntity
    {
        if (!$this->messageBusCacheInvalidator->isValid(self::$cachedCategoryTimestamp) || null === self::$cachedCategoryParentIds) {
            self::$cachedCategoryParentIds = [];

            /** @var CategoryEntity $categoryEntity */
            foreach ($this->categoryRepository->search(new Criteria()) as $categoryEntity) {
                self::$cachedCategoryParentIds[$categoryEntity->getId()] = $categoryEntity->getParentId();
            }

            self::$cachedCategoryTimestamp = time();
        }

        if(empty(self::$cachedCategoryParentIds)) {
            return null;
        }

        $categoryParentId = self::$cachedCategoryParentIds[$categoryId];
        /** Return null, if there is no parent available */
        if (null === $categoryParentId) {
            return null;
        }

        /** Otherwise try to fetch a inherit config */
        /** Return the current seo bulk configuration, if its not null and a template is defined */
        $dreiscSeoBulkEntity = $this->getCachedSeoBulk($categoryParentId, $area, $seoOption, $languageId, $salesChannelId, true);

        if (null !== $dreiscSeoBulkEntity && null !== $dreiscSeoBulkEntity->getDreiscSeoBulkTemplateId()) {
            return $dreiscSeoBulkEntity;
        }

        /** There is no seo bulk setting for the given parent category, so we look for a deep-parent category which inherits a template */
        return $this->getResponsibleParentSeoBulk($categoryParentId, $area, $seoOption, $languageId, $salesChannelId);
    }

    /**
     * We load all available seo bulks only one time for performance reasons
     *
     * @param string|null $salesChannelId
     * @return EntitySearchResult|null
     */
    protected function getCachedSeoBulk(string $categoryId, string $area, string $seoOption, string $languageId, string $salesChannelId = null, ?bool $inherit = null): ?DreiscSeoBulkEntity
    {
        if (!$this->messageBusCacheInvalidator->isValid(self::$cachedSeoBulksTimestamp) || null === self::$cachedSeoBulks) {
            self::$cachedSeoBulks = $this->search(
                (new Criteria())
                    ->addAssociation(DreiscSeoBulkEntity::DREISC_SEO_BULK_TEMPLATE__PROPERTY_NAME)
                    ->addAssociation(DreiscSeoBulkEntity::CATEGORY__PROPERTY_NAME)
                    ->addAssociation('overwriteCustomField')
            );

            self::$cachedSeoBulksTimestamp = time();
        }

            /** @var DreiscSeoBulkEntity $cachedSeoBulk */
            foreach(self::$cachedSeoBulks as $cachedSeoBulk) {
                if (
                    $cachedSeoBulk->getCategoryId() === $categoryId &&
                    $cachedSeoBulk->getArea() === $area &&
                    $cachedSeoBulk->getSeoOption() === $seoOption &&
                    $cachedSeoBulk->getLanguageId() === $languageId &&
                    $cachedSeoBulk->getSalesChannelId() === $salesChannelId
                ) {
                    if (!is_bool($inherit)) {
                        return $cachedSeoBulk;
                    }

                    if ($cachedSeoBulk->getInherit() === $inherit) {
                        return $cachedSeoBulk;
                    }
                }
            }

            return null;
    }

    /**
     * Deletes all seo bulks with the given template id
     *
     * @param $seoBulkTemplateId
     * @throws InconsistentCriteriaIdsException
     */
    public function deleteBySeoBulkTemplateId($seoBulkTemplateId)
    {
        $this->deleteByCriteria((new Criteria())
            ->addFilter(
                new EqualsFilter(
                    DreiscSeoBulkEntity::DREISC_SEO_BULK_TEMPLATE_ID__PROPERTY_NAME,
                    $seoBulkTemplateId
                )
            ));
    }

    private function fetchProductEntityByCollection(string $productId, ?ProductCollection $productCollection): ?ProductEntity
    {
        if (null === $productCollection) {
            return null;
        }

        /** @var ProductEntity $productEntity */
        foreach($productCollection as $productEntity) {
            if ($productEntity->getId() === $productId) {
                return $productEntity;
            }
        }

        return null;
    }
}

