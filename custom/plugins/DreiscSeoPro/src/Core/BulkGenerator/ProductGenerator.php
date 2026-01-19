<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator;

use DreiscSeoPro\Core\BulkGenerator\Generator\AbstractGenerator;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Exception\ReferenceEntityNotFoundException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\TemplateGeneratorHelper;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\TemplateGeneratorInterface;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEntity;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkRepository;
use DreiscSeoPro\Core\Content\Language\LanguageRepository;
use DreiscSeoPro\Core\Content\Product\ProductIndexer;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Content\SalesChannel\SalesChannelRepository;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdater;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ProductGenerator extends AbstractGenerator
{
    final public const PRODUCT_ASSOCIATIONS = [
        'categories',
        'manufacturer',
        'deliveryTime',
        'properties.group',
        'options.group',
        'mainCategories'
    ];

    /**
     * @param LanguageRepository $languageRepository
     * @param SalesChannelRepository $salesChannelRepository
     * @param DreiscSeoBulkRepository $dreiscSeoBulkRepository
     * @param TemplateGeneratorInterface $templateGenerator
     * @param SeoDataSaver $seoDataSaver
     * @param BulkUpdater $bulkUpdater
     * @param ProductRepository $productRepository
     * @param TemplateGenerator\TemplateGeneratorHelper $templateGeneratorHelper
     */
    public function __construct(
        LanguageRepository $languageRepository,
        SalesChannelRepository $salesChannelRepository,
        DreiscSeoBulkRepository $dreiscSeoBulkRepository,
        TemplateGeneratorInterface $templateGenerator,
        SeoDataSaver $seoDataSaver,
        BulkUpdater $bulkUpdater,
        TemplateGeneratorHelper $templateGeneratorHelper,
        AiTemplateGenerator $aiTemplateGenerator,
        private readonly ProductRepository $productRepository,
        private readonly ProductSeoDataFetcher $productSeoDataFetcher
    )
    {
        parent::__construct(
            $languageRepository,
            $salesChannelRepository,
            $dreiscSeoBulkRepository,
            $templateGenerator,
            $seoDataSaver,
            $bulkUpdater,
            $templateGeneratorHelper,
            $aiTemplateGenerator
        );
    }

    /**
     * Generate the bulk settings for the given product ids
     *
     * @param array $productIds
     * @param array $languageIds
     * @param array $seoOptions
     * @param array $bulkGeneratorTypes
     * @throws InconsistentCriteriaIdsException
     * @throws ReferenceEntityNotFoundException
     * @throws SeoDataSaver\Exception\UnknownAreaException
     */
    public function generate(array $productIds, array $languageIds = [], array $seoOptions = [], array $bulkGeneratorTypes = []): void
    {
        /** We disable the bulk generator of the indexer */
        ProductIndexer::disableBulkIndexer();

        $this->iterateReferenceIds($productIds, $languageIds, $seoOptions, $bulkGeneratorTypes);

        /** Enable the indexer again */
        ProductIndexer::disableBulkIndexer(false);
    }

    /**
     * @return string
     */
    protected function getArea(): string
    {
        return DreiscSeoBulkEnum::AREA__PRODUCT;
    }

    /**
     * @param string $referenceId
     * @param string $languageId
     * @param string $seoOption
     * @param string|null $salesChannelId
     * @param Context $context
     * @param EntityCollection $referenceEntityCollection
     * @return SeoDataSaverStruct|null
     */
    protected function generateItem(string $referenceId, string $languageId, string $seoOption, string $bulkGeneratorType, ?string $salesChannelId, Context $context, EntityCollection $referenceEntityCollection, ?SeoDataFetchResultStruct $currentSeoDataFetchResultStruct): ?SeoDataSaverStruct
    {
        $referenceEntity = $referenceEntityCollection->get($referenceId);
        $dreiscSeoBulkEntity = $this->dreiscSeoBulkRepository->getResponsibleProductSeoBulkRespectPriority(
            $referenceId,
            $seoOption,
            $languageId,
            $salesChannelId,
            $referenceEntityCollection
        );

        /** Abort, if there is no template available for this category */
        if(null === $dreiscSeoBulkEntity || null === $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()) {
            return null;
        }

        if (
            ($dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()?->getAiPrompt() && $bulkGeneratorType !== DreiscSeoBulkEnum::BULK_GENERATOR_TYPE__AI) ||
            (!$dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()?->getAiPrompt() && $bulkGeneratorType !== DreiscSeoBulkEnum::BULK_GENERATOR_TYPE__DEFAULT)
        ) {
            /** Skip because the bulk generator type does not match the template */
            return null;
        }

        $seoDataSaverStruct = new SeoDataSaverStruct(
            $this->getArea(),
            $referenceId,
            $seoOption,
            $languageId,
            $salesChannelId,
            $dreiscSeoBulkEntity->getOverwrite(),
            $this->getCustomField($dreiscSeoBulkEntity),
            $this->getCustomFieldValue($dreiscSeoBulkEntity, $referenceEntity, $languageId)
        );

        if (false === $this->seoDataSaver->isValidItem($seoDataSaverStruct, $currentSeoDataFetchResultStruct)) {
            return null;
        }

        /** Render the template */
        $seoDataSaverStruct->setNewValue(
            $this->generateTemplate(
                $dreiscSeoBulkEntity,
                $referenceId,
                $seoOption,
                $bulkGeneratorType,
                $languageId,
                $referenceEntityCollection,
                $salesChannelId,
                $context
            )
        );

        if(null === $seoDataSaverStruct->getNewValue()) {
            return null;
        }

        return $seoDataSaverStruct;
    }

    protected function getCustomFieldValue(?DreiscSeoBulkEntity $dreiscSeoBulkEntity, ?Entity $referenceEntity, string $languageId)
    {
        if (
            null === $dreiscSeoBulkEntity ||
            null === $referenceEntity ||
            null === $dreiscSeoBulkEntity->getOverwriteCustomField() ||
            empty($dreiscSeoBulkEntity->getOverwriteCustomField()->getName())
        ) {
            return false;
        }

        if ($referenceEntity instanceof ProductEntity && null !== $referenceEntity->getParentId())
        {
            /** In this case we need to check the custom field value of the variant */
            $context = $this->fetchLanguageContext($languageId);
            $context->setConsiderInheritance(false);
            $variantEntity = $this->productRepository->get($referenceEntity->getId(), null, $context);
            $customFields = $variantEntity->getCustomFields();

            if (empty($customFields) || empty($customFields[$dreiscSeoBulkEntity->getOverwriteCustomField()->getName()])) {
                return false;
            }

            return true;
        }

        return parent::getCustomFieldValue($dreiscSeoBulkEntity, $referenceEntity, $languageId);
    }

    /**
     * @return EntityRepository
     */
    protected function getEntityRepository(): EntityRepository
    {
        return $this->productRepository;
    }

    /**
     * @param array $referenceIds
     * @return Criteria
     */
    protected function getEntitySearchCriteria(array $referenceIds): Criteria
    {
        return (new Criteria($referenceIds))
            ->addAssociations(self::PRODUCT_ASSOCIATIONS)
            ->addAssociation('categories')
            ->addAssociation('mainCategories');
    }

    protected function fetchSeoDataCollection(array $referenceIds, ?string $salesChannelId, string $languageId, array $seoOptions): array
    {
        return $this->productSeoDataFetcher->fetchList($referenceIds, $languageId, $salesChannelId, true);
    }

    protected function setCustomField(array $customFieldUpdates)
    {
        $updateData = [];

        foreach($customFieldUpdates as $customFieldUpdate) {
            $updateData[] = [
                'id' => $customFieldUpdate->getReferenceId(),
                'translations' => [
                    $customFieldUpdate->getLanguageId() => [
                        'customFields' => [
                            $customFieldUpdate->getOverwriteCustomField() => true
                        ]
                    ]
                ]

            ];
        }

        $this->productRepository->update($updateData);
    }
}
