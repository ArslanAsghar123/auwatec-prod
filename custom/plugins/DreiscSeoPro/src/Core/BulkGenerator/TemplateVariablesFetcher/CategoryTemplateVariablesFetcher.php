<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\TemplateVariablesFetcher;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Exception\ReferenceEntityNotFoundException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct\TemplateGeneratorStruct;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\CategorySeoDataFetcher;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;

class CategoryTemplateVariablesFetcher
{
    public function __construct(private readonly CategoryRepository $categoryRepository, private readonly CommonTemplateVariablesFetcher $commonTemplateVariablesFetcher, private readonly CategorySeoDataFetcher $categorySeoDataFetcher)
    {
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws ReferenceEntityNotFoundException
     * @throws DBALException
     * @throws InvalidUuidException
     */
    public function fetchVariables(TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext, string $template): array
    {
        /** Fetch the category entity */
        $categoryEntity = $this->getCategoryEntity($templateGeneratorStruct, $languageChainContext);

        return $this->fetchVariablesByTranslatedCategoryEntity($categoryEntity, $templateGeneratorStruct, $languageChainContext, $template);
    }

    public function fetchVariablesByTranslatedCategoryEntity(Entity $translatedCategoryEntity, TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext, string $template)
    {
        /** Throw exception, if the entity could not load */
        if (null === $translatedCategoryEntity) {
            throw new ReferenceEntityNotFoundException($templateGeneratorStruct->getArea(), $templateGeneratorStruct->getReferenceId());
        }

        /** Load the parent category if necessary */
        $translatedCategoryEntity = $this->getParentCategory($translatedCategoryEntity, $template, $templateGeneratorStruct, $languageChainContext);

        /** Load the parent categories if necessary */
        $parentCategories = $this->getParentCategories($translatedCategoryEntity, $template, $templateGeneratorStruct, $languageChainContext);

        /** Load the child categories if necessary */
        $childCategories = $this->getChildCategories($translatedCategoryEntity, $template, $templateGeneratorStruct, $languageChainContext);

        /** Fetch the seo data of the category */
        $productSeoDataFetchResultStruct = $this->categorySeoDataFetcher->fetch(
            $templateGeneratorStruct->getReferenceId(),
            $templateGeneratorStruct->getLanguageId(),
            $templateGeneratorStruct->getSalesChannelId(),
            $translatedCategoryEntity
        );

        /** Fetch the seo info of the parent category, if exists */
        if (null !== $translatedCategoryEntity->getParentId()) {
            $categoryParentSeoDataFetchResultStruct = $this->categorySeoDataFetcher->fetch(
                $translatedCategoryEntity->getParentId(),
                $templateGeneratorStruct->getLanguageId(),
                $templateGeneratorStruct->getSalesChannelId()
            );
        } else {
            $categoryParentSeoDataFetchResultStruct = null;
        }

        /** Set the variables */
        $variables['category'] = $translatedCategoryEntity;
        $variables['categorySeo'] = $productSeoDataFetchResultStruct;
        $variables['parentCategories'] = $parentCategories;
        $variables['childCategories'] = $childCategories;

        if (null !== $categoryParentSeoDataFetchResultStruct) {
            $variables['categoryParentSeo'] = $categoryParentSeoDataFetchResultStruct;
        }

        /** Fetch the common variables */
        $variables = $this->commonTemplateVariablesFetcher->fetch(
            $variables,
            $templateGeneratorStruct,
            $languageChainContext,
            $template
        );

        return $variables;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getCategoryEntity(TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext): CategoryEntity
    {
        return $this->categoryRepository->get(
            $templateGeneratorStruct->getReferenceId(),
            null,
            $languageChainContext,
            true
        );
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getParentCategory(CategoryEntity $categoryEntity, string $template, TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext): CategoryEntity
    {
        /** Abort, if the "category.parent" variable was not found in the template */
        if(!str_contains($template, 'category.parent')) {
            return $categoryEntity;
        }

        /** Abort, if there is no parent */
        if (null === $categoryEntity->getParentId()) {
            return $categoryEntity;
        }

        /** Fetch the data of the parent category */
        $parentCategory = $this->getCategoryEntity(new TemplateGeneratorStruct(
            $templateGeneratorStruct->getArea(),
            $categoryEntity->getParentId(),
            $templateGeneratorStruct->getSeoOption(),
            $templateGeneratorStruct->getLanguageId(),
            $templateGeneratorStruct->getSalesChannelId(),
            $templateGeneratorStruct->isSpaceless(),
            null
        ), $languageChainContext);

        /** Set the parent entity */
        $categoryEntity->setParent($parentCategory);

        return $categoryEntity;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getParentCategories(CategoryEntity $categoryEntity, string $template, TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext): ?array
    {
        /** Abort, if the "parentCategories" variable was not found in the template */
        if(!str_contains($template, 'parentCategories')) {
            return null;
        }

        /** Abort, if no path is available */
        if (null === $categoryEntity->getPath()) {
            return null;
        }

        /** Extract the parent ids from the path */
        $parentCategoryIds = array_filter(explode('|', (string) $categoryEntity->getPath()));

        /** Abort, if there are not parent categories */
        if(empty($parentCategoryIds)) {
            return null;
        }

        /** Reverse the direction */
        $parentCategoryIds = array_reverse($parentCategoryIds);

        /** Fetch the data of the parent categories */
        $parentCategories = [];
        foreach($parentCategoryIds as $parentCategoryId) {
            $parentCategory = $this->getCategoryEntity(new TemplateGeneratorStruct(
                $templateGeneratorStruct->getArea(),
                $parentCategoryId,
                $templateGeneratorStruct->getSeoOption(),
                $templateGeneratorStruct->getLanguageId(),
                $templateGeneratorStruct->getSalesChannelId(),
                $templateGeneratorStruct->isSpaceless()
            ), $languageChainContext);

            if (null !== $parentCategory) {
                $parentCategories[] = $parentCategory;
            }
        }

        return $parentCategories;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getChildCategories(CategoryEntity $categoryEntity, string $template, TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext): ?array
    {
        /** Abort, if the "parentCategories" variable was not found in the template */
        if(!str_contains($template, 'childCategories')) {
            return null;
        }

        /** Fetch the ids of child categories */
        $childCategoryIdSearchResult = $this->categoryRepository->getChildIds($categoryEntity->getId());

        /** Abort, if no child categories available */
        if (0 === $childCategoryIdSearchResult->getTotal()) {
            return null;
        }

        /** Fetch the data of the child categories */
        $childCategories = [];
        foreach($childCategoryIdSearchResult->getIds() as $childCategoryId) {
            $childCategory = $this->getCategoryEntity(new TemplateGeneratorStruct(
                $templateGeneratorStruct->getArea(),
                $childCategoryId,
                $templateGeneratorStruct->getSeoOption(),
                $templateGeneratorStruct->getLanguageId(),
                $templateGeneratorStruct->getSalesChannelId(),
                $templateGeneratorStruct->isSpaceless()
            ), $languageChainContext);

            if (null !== $childCategory) {
                $childCategories[] = $childCategory;
            }
        }

        return $childCategories;
    }
}
