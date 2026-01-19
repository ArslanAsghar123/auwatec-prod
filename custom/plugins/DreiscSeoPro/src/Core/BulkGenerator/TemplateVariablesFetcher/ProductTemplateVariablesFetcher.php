<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\TemplateVariablesFetcher;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\BulkGenerator\Generator\AbstractGenerator;
use DreiscSeoPro\Core\BulkGenerator\ProductGenerator;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Exception\ReferenceEntityNotFoundException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct\TemplateGeneratorStruct;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Content\Language\LanguageRepository;
use DreiscSeoPro\Core\Content\MainCategory\MainCategoryRepository;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\CategorySeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\System\Language\LanguageEntity;
use Symfony\Component\Stopwatch\Stopwatch;

class ProductTemplateVariablesFetcher
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository, private readonly ProductSeoDataFetcher $productSeoDataFetcher, private readonly MainCategoryRepository $mainCategoryRepository, private readonly CategorySeoDataFetcher $categorySeoDataFetcher, private readonly CommonTemplateVariablesFetcher $commonTemplateVariablesFetcher)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws ReferenceEntityNotFoundException
     * @throws DBALException
     * @throws InvalidUuidException
     */
    public function fetchVariables(TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext, string $template): array
    {
        /** Fetch the product entity */
        $productEntity = $this->getProductEntity($templateGeneratorStruct, $languageChainContext);

        return $this->fetchVariablesByTranslatedProductEntity($productEntity, $templateGeneratorStruct, $languageChainContext, $template);
    }

    /**
     * @param $languageChainContext
     * @throws DBALException
     */
    public function fetchVariablesByTranslatedProductEntity(Entity $translatedProductEntity, TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext, string $template): array
    {
        /** Throw exception, if the entity could not load */
        if (null === $translatedProductEntity) {
            throw new ReferenceEntityNotFoundException($templateGeneratorStruct->getArea(), $templateGeneratorStruct->getReferenceId());
        }

        /**
         * @TODO:
         * Bei Problemen mit der Vererbung, den letzten Parameter $translatedProductEntity durch null ersetzen.
         * Bzw. in diesem Fall in der @see AbstractGenerator::iterateReferenceIds die $referenceEntityCollection
         * auch einmal ohne ConsiderInheritance laden.
         */

        /** Fetch the seo data of the product */
        $productSeoDataFetchResultStruct = $this->productSeoDataFetcher->fetch(
            $templateGeneratorStruct->getReferenceId(),
            $templateGeneratorStruct->getLanguageId(),
            $templateGeneratorStruct->getSalesChannelId(),
            false,
            $translatedProductEntity
        );

        /** Load the main category of the product */

        if (null === $templateGeneratorStruct->getSalesChannelId()) {
            /** A sales channel id is required to determine a main category. Therefore it cannot be read out for meta titles etc.! */
            $mainCategoryEntity = null;
        } else {
            $mainCategoryEntity = $this->mainCategoryRepository->getProductMainCategory(
                $translatedProductEntity,
                $templateGeneratorStruct->getSalesChannelId(),
                $templateGeneratorStruct->getpreferredCategoryId()
            );
        }

        if (null !== $mainCategoryEntity) {
            /** Fetch the seo data of the main category */
            $mainCategorySeoDataFetchResultStruct = $this->categorySeoDataFetcher->fetch(
                $mainCategoryEntity->getId(),
                $templateGeneratorStruct->getLanguageId(),
                $templateGeneratorStruct->getSalesChannelId(),
                $mainCategoryEntity
            );
        } else {
            $mainCategorySeoDataFetchResultStruct = null;
        }

        /** Set the variables */
        $variables['isVariant'] = null !== $translatedProductEntity->getParentId();
        $variables['product'] = $translatedProductEntity;
        $variables['productSeo'] = $productSeoDataFetchResultStruct;

        if (null !== $mainCategoryEntity) {
            $variables['mainCategory'] = $mainCategoryEntity;
        }

        if (null !== $mainCategorySeoDataFetchResultStruct) {
            $variables['mainCategorySeo'] = $mainCategorySeoDataFetchResultStruct;
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
     * @param string $template
     */
    private function getProductEntity(TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext): ProductEntity
    {
        $productEntity = $this->productRepository->get(
            $templateGeneratorStruct->getReferenceId(),
            ProductGenerator::PRODUCT_ASSOCIATIONS,
            $languageChainContext
        );

        return $productEntity;
    }
}
