<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\BulkGenerator\AiTemplateGenerator;
use DreiscSeoPro\Core\BulkGenerator\Exception\TemplateGeneratorException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Exception\ReferenceEntityNotFoundException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct\TemplateGeneratorStruct;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\TemplateGeneratorHelper;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\TemplateGeneratorInterface;
use DreiscSeoPro\Core\Content\Category\CategoryIndexer;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEntity;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkRepository;
use DreiscSeoPro\Core\Content\Language\LanguageRepository;
use DreiscSeoPro\Core\Content\Product\ProductIndexer;
use DreiscSeoPro\Core\Content\SalesChannel\SalesChannelRepository;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdater;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;
use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Twig\Error\RuntimeError;

abstract class AbstractGenerator
{
    const SEO_DATA_DEFAULT = 'default';

    /**
     * @param LanguageRepository $languageRepository
     * @param TemplateGeneratorInterface $templateGenerator
     */
    public function __construct(
        protected readonly LanguageRepository $languageRepository,
        protected readonly SalesChannelRepository $salesChannelRepository,
        protected readonly DreiscSeoBulkRepository $dreiscSeoBulkRepository,
        protected readonly TemplateGeneratorInterface $templateGenerator,
        protected readonly SeoDataSaver $seoDataSaver,
        protected readonly BulkUpdater $bulkUpdater,
        protected readonly TemplateGeneratorHelper $templateGeneratorHelper,
        protected readonly AiTemplateGenerator $aiTemplateGenerator
    ) {}

    /**
     * Creates the bulk settings for the categories with the given ids
     *
     * @throws InconsistentCriteriaIdsException
     * @throws ReferenceEntityNotFoundException
     * @throws SeoDataSaver\Exception\UnknownAreaException
     * @throws DBALException
     */
    public function iterateReferenceIds(array $referenceIds, array $languageIds = [], array $seoOptions = [], array $bulkGeneratorTypes = []): void
    {
        $seoDataSaverStructs = [];
        $salesChannelIds = $this->salesChannelRepository->searchIds(new Criteria(), null, true)->getIds();

        if(empty($languageIds)) {
            $languageIdSearchResult = $this->languageRepository->searchIds(new Criteria(), null, true);
            $languageIds = $languageIdSearchResult->getIds();
        }

        if(empty($seoOptions)) {
            $seoOptions = DreiscSeoBulkEnum::VALID_SEO_OPTIONS;
        }

        if(empty($bulkGeneratorTypes)) {
            $bulkGeneratorTypes = DreiscSeoBulkEnum::VALID_BULK_GENERATOR_TYPES;
        }

        /** Load the seo data of the current reference ids */
        $currentSeoData = $this->loadSeoDataCollection($referenceIds, $languageIds, $seoOptions, $salesChannelIds);

        /** For each language id */
        foreach($languageIds as $languageId) {

            /** Load the details of the current entities */
            $context = $this->fetchLanguageContext($languageId);
            $referenceEntityCollection = $this->fetchReferenceEntityCollection($referenceIds, $context);

            /** For each category id */
            foreach($referenceIds as $referenceId) {
                /** For each seo option */
                foreach($seoOptions as $seoOption) {
                    /** For each bulk generator type */
                    foreach($bulkGeneratorTypes as $bulkGeneratorType) {
                        /** For each sales channel, if the seo option requires a sales channel */
                        if (in_array($seoOption, DreiscSeoBulkEnum::SEO_OPTIONS_WHICH_REQUIRED_SALES_CHANNEL, true)) {
                            foreach($salesChannelIds as $salesChannelId) {
                                $seoDataSaverStructs[] = $this->generateItem($referenceId, $languageId, $seoOption, $bulkGeneratorType, $salesChannelId, $context, $referenceEntityCollection, $currentSeoData[$languageId][$salesChannelId][$referenceId] ?? null);
                            }
                        }
                        /** Otherwise, we pass NULL as sales channel */
                        else  {
                            $seoDataSaverStructs[] = $this->generateItem($referenceId, $languageId, $seoOption, $bulkGeneratorType, null, $context, $referenceEntityCollection, $currentSeoData[$languageId][self::SEO_DATA_DEFAULT][$referenceId] ?? null);
                        }
                    }
                }
            }
        }

        $bulkUpdaterStruct = new BulkUpdaterStruct();
        $seoDataSaverStructs = array_filter($seoDataSaverStructs);

        $this->seoDataSaver->save($seoDataSaverStructs, $bulkUpdaterStruct);

        /** Run the updates */
        $this->bulkUpdater->update($bulkUpdaterStruct);

        /** Set custom fields */
        $this->setCustomFields($seoDataSaverStructs);
    }

    protected function loadSeoDataCollection(array $referenceIds, array $languageIds, array $seoOptions, array $salesChannelIds): array
    {
        $seoData = [];

        /** Check if is necessary to load the seo data for all sales channels */
        $isSalesChannelRequired = false;
        foreach($seoOptions as $seoOption) {
            if (in_array($seoOption, DreiscSeoBulkEnum::SEO_OPTIONS_WHICH_REQUIRED_SALES_CHANNEL, true)) {
                $isSalesChannelRequired = true;
                break;
            }
        }

        foreach($languageIds as $languageId) {
            $seoData[$languageId][self::SEO_DATA_DEFAULT] = $this->fetchSeoDataCollection($referenceIds, null, $languageId, $seoOptions);

            if ($isSalesChannelRequired) {
                foreach($salesChannelIds as $salesChannelId) {
                    $seoData[$languageId][$salesChannelId] = $this->fetchSeoDataCollection($referenceIds, $salesChannelId, $languageId, $seoOptions);
                }
            }
        }

        return $seoData;
    }

    protected function getCustomField(?DreiscSeoBulkEntity $dreiscSeoBulkEntity)
    {
        if (null === $dreiscSeoBulkEntity) {
            return null;
        }

        return $dreiscSeoBulkEntity->getOverwriteCustomField()?->getName();
    }

    protected function getCustomFieldValue(?DreiscSeoBulkEntity $dreiscSeoBulkEntity, ?Entity $referenceEntity, string $languageId)
    {
        if (
            null === $dreiscSeoBulkEntity ||
            null === $referenceEntity ||
            null === $dreiscSeoBulkEntity->getOverwriteCustomField() ||
            empty($dreiscSeoBulkEntity->getOverwriteCustomField()->getName()) ||
            null === $referenceEntity->getCustomFields()
        ) {
            return false;
        }

        $customFields = $referenceEntity->getCustomFields();

        if (empty($customFields) || empty($customFields[$dreiscSeoBulkEntity->getOverwriteCustomField()->getName()])) {
            return false;
        }

        return true;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    protected function fetchResponsibleSeoBulkEntity(string $referenceId, string $seoOption, string $languageId, ?string $salesChannelId): ?DreiscSeoBulkEntity
    {
        return $this->dreiscSeoBulkRepository->getResponsibleSeoBulk(
            $referenceId,
            $this->getArea(),
            $seoOption,
            $languageId,
            $salesChannelId
        );
    }

    protected function generateTemplate(DreiscSeoBulkEntity $dreiscSeoBulkEntity, string $referenceId, string $seoOption, string $bulkGeneratorType, string $languageId, EntityCollection $referenceEntityCollection, ?string $salesChannelId, Context $context): ?string
    {
        if (null === $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate() || null === $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()->getTemplate()) {
            return null;
        }

        try {
            $generatedTemplate = $this->templateGenerator->generateTemplate(
                new TemplateGeneratorStruct(
                    $this->getArea(),
                    $referenceId,
                    $seoOption,
                    $languageId,
                    $salesChannelId,
                    $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()->getSpaceless(),
                    $dreiscSeoBulkEntity->getCategoryId()
                ),
                $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()->getTemplate(),
                $this->getReferenceEntity($referenceId, $referenceEntityCollection),
                $context
            );
        } catch (RuntimeError $twigRuntimeError) {
            throw new TemplateGeneratorException(
                $twigRuntimeError->getMessage(),
                $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()->getName(),
                $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()->getArea(),
                $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()->getSeoOption()
            );
        }

        /** If the bulk generator type is AI, we generate the AI template */
        if(DreiscSeoBulkEnum::BULK_GENERATOR_TYPE__AI === $bulkGeneratorType) {
            $generatedTemplate = $this->aiTemplateGenerator->generate($generatedTemplate, $seoOption);
        }

        return $generatedTemplate;
    }

    abstract public function generate(array $referenceIds, array $languageIds, array $seoOptions): void;

    abstract protected function getArea() : string;

    /**
     * @param string $referenceId
     * @param string $languageId
     * @param string $seoOption
     * @param string|null $salesChannelId
     * @param Context $context
     * @param EntityCollection $referenceEntityCollection
     * @param SeoDataFetchResultStruct[]|null $currentSeoData
     * @return SeoDataSaverStruct|null
     */
    abstract protected function generateItem(string $referenceId, string $languageId, string $seoOption, string $bulkGeneratorType, ?string $salesChannelId, Context $context, EntityCollection $referenceEntityCollection, ?SeoDataFetchResultStruct $currentSeoDataFetchResultStruct): ?SeoDataSaverStruct;

    abstract protected function getEntityRepository(): EntityRepository;

    abstract protected function getEntitySearchCriteria(array $referenceIds): Criteria;

    abstract protected function fetchSeoDataCollection(array $referenceIds, ?string $salesChannelId, string $languageId, array $seoOptions);

    protected function fetchReferenceEntityCollection(array $referenceIds, Context $context): EntityCollection
    {
        return $this->getEntityRepository()->search(
            $this->getEntitySearchCriteria($referenceIds),
            $context
        )->getEntities();
    }

    /**
     * @throws DBALException
     */
    protected function fetchLanguageContext(string $languageId): Context
    {
        /** Create a context which active inheritance */
        $context = $this->templateGeneratorHelper->createLanguageChainContext($languageId);
        $context->setConsiderInheritance(true);

        return $context;
    }

    private function getReferenceEntity(string $referenceId, ?EntityCollection $referenceEntityCollection): ?Entity
    {
        if (null === $referenceEntityCollection) {
            return null;
        }

        /** @var Entity $referenceEntity */
        foreach($referenceEntityCollection as $referenceEntity) {
            if ($referenceEntity->getId() === $referenceId) {
                return $referenceEntity;
            }
        }

        return null;
    }

    private function setCustomFields(array $seoDataSaverStructs)
    {
        $customFieldUpdates = [];

        if(empty($seoDataSaverStructs)) {
            return;
        }
        /** @var SeoDataSaverStruct $seoDataSaverStruct */
        foreach($seoDataSaverStructs as $seoDataSaverStruct) {
            if (!in_array($seoDataSaverStruct->getOverwrite(), [DreiscSeoBulkEnum::OVERWRITE__EMPTY_AND_CUSTOM_FIELD_NOT_SET, DreiscSeoBulkEnum::OVERWRITE__EMPTY_OR_CUSTOM_FIELD_NOT_SET])) {
                continue;
            }

            $customFieldUpdates[] = $seoDataSaverStruct;
        }

        if(empty($customFieldUpdates)) {
            return;
        }

        $this->setCustomField($customFieldUpdates);
    }

    abstract protected function setCustomField(array $customFieldUpdates);
}
