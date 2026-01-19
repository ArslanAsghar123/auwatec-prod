<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Import;

use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportLog\DreiscSeoRedirectImportExportLogEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportLog\DreiscSeoRedirectImportExportLogRepository;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEnum;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Content\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainRepository;
use DreiscSeoPro\Core\Foundation\Seo\SeoUrlParser;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class RowImporter
{
    final public const HEADER__ACTIVE = 'active';
    final public const HEADER__HTTP_STATUS_CODE = 'httpStatusCode';
    final public const HEADER__PARAMETER_FORWARDING = 'parameterForwarding';
    final public const HEADER__SOURCE_INTERNAL_URL = 'sourceInternalUrl';
    final public const HEADER__SOURCE_PRODUCT_NUMBER = 'sourceProductNumber';
    final public const HEADER__SOURCE_PRODUCT_ID = 'sourceProductId';
    final public const HEADER__SOURCE_CATEGORY_ID = 'sourceCategoryId';
    final public const HEADER__TARGET_INTERNAL_URL = 'targetInternalUrl';
    final public const HEADER__TARGET_EXTERNAL_URL = 'targetExternalUrl';
    final public const HEADER__TARGET_PRODUCT_NUMBER = 'targetProductNumber';
    final public const HEADER__TARGET_PRODUCT_ID = 'targetProductId';
    final public const HEADER__TARGET_CATEGORY_ID = 'targetCategoryId';

    final public const HEADER__SOURCE_RESTRICTION_DOMAINS = 'sourceRestrictionDomains';
    final public const HEADER__TARGET_DEVIATING_DOMAIN = 'targetDeviatingDomain';

    final public const AVAILABLE_HEADERS = [
        self::HEADER__ACTIVE,
        self::HEADER__HTTP_STATUS_CODE,
        self::HEADER__SOURCE_INTERNAL_URL,
        self::HEADER__SOURCE_PRODUCT_NUMBER,
        self::HEADER__SOURCE_CATEGORY_ID,
        self::HEADER__SOURCE_PRODUCT_ID,
        self::HEADER__TARGET_INTERNAL_URL,
        self::HEADER__TARGET_EXTERNAL_URL,
        self::HEADER__TARGET_PRODUCT_NUMBER,
        self::HEADER__TARGET_CATEGORY_ID,
        self::HEADER__TARGET_PRODUCT_ID,
        self::HEADER__TARGET_DEVIATING_DOMAIN
    ];

    final public const ERROR__SOURCE_ALREADY_EXISTS = 'sourceAlreadyExists';

    final public const ERROR__MISSING_SOURCE = 'missingSource';
    final public const ERROR__MISSING_TARGET = 'missingTarget';
    final public const ERROR__INVALID_HTTP_STATUS_CODE = 'invalidHttpStatusCode';

    final public const ERROR__INVALID_SOURCE_PRODUCT_NUMBER = 'invalidSourceProductNumber';
    final public const ERROR__INVALID_SOURCE_INTERNAL_URL = 'invalidSourceInternalUrl';
    final public const ERROR__INVALID_SOURCE_INTERNAL_URL_EMPTY_BASE_URL = 'invalidSourceInternalUrlEmptyBaseUrl';
    final public const ERROR__INVALID_SOURCE_CATEGORY_ID__NOT_FOUND = 'invalidSourceCategoryIdNotFound';
    final public const ERROR__INVALID_SOURCE_CATEGORY_ID__UUID_FORMAT = 'invalidSourceCategoryIdUuidFormat';
    final public const ERROR__INVALID_SOURCE_PRODUCT_ID__NOT_FOUND = 'invalidSourceProductIdNotFound';
    final public const ERROR__INVALID_SOURCE_PRODUCT_ID__UUID_FORMAT = 'invalidSourceProductIdUuidFormat';

    final public const ERROR__INVALID_TARGET_PRODUCT_NUMBER = 'invalidTargetProductNumber';
    final public const ERROR__INVALID_TARGET_INTERNAL_URL = 'invalidTargetInternalUrl';
    final public const ERROR__INVALID_TARGET_INTERNAL_URL_EMPTY_BASE_URL = 'invalidTargetInternalUrlEmptyBaseUrl';
    final public const ERROR__INVALID_TARGET_CATEGORY_ID__NOT_FOUND = 'invalidTargetCategoryIdNotFound';
    final public const ERROR__INVALID_TARGET_CATEGORY_ID__UUID_FORMAT = 'invalidTargetCategoryIdUuidFormat';
    final public const ERROR__INVALID_TARGET_PRODUCT_ID__NOT_FOUND = 'invalidTargetProductIdNotFound';
    final public const ERROR__INVALID_TARGET_PRODUCT_ID__UUID_FORMAT = 'invalidTargetProductIdUuidFormat';

    final public const ERROR__INVALID_DEVIATING_DOMAIN = 'invalidDeviatingDomain';
    final public const ERROR__INVALID_RESTRICTION_DOMAIN = 'invalidRestrictionDomain';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var SalesChannelDomainRepository
     */
    private $salesChannelDomainRepository;



    private ?DreiscSeoRedirectImportExportLogEntity $dreiscSeoRedirectImportExportLogEntity = null;

    private ?DreiscSeoRedirectEntity $dreiscSeoRedirectEntity = null;

    private ?array $errors = null;

    /**
     * @param ProductRepository $productRepository
     * @param SalesChannelDomainRepository $salesChannelDomainRepository
     */
    public function __construct(private readonly DreiscSeoRedirectImportExportLogRepository $dreiscSeoRedirectImportExportLogRepository, private readonly DreiscSeoRedirectRepository $dreiscSeoRedirectRepository, ProductRepository $productRepository, private readonly SeoUrlParser $seoUrlParser, private readonly CategoryRepository $categoryRepository, SalesChannelDomainRepository $salesChannelDomainRepository)
    {
        $this->productRepository = $productRepository;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
    }

    public function import(array $row, int $rowIndex): void
    {
        /** Trim keys and values */
        $row = $this->trimKeysAndValues($row);

        /** Reset */
        $this->prepareImport($row, $rowIndex);

        /** Check the headers */
        $this->checkHeader($row);

        /** Create default redirect entity */
        $this->dreiscSeoRedirectEntity = $this->createBaseDreiscSeoRedirectEntity($row);

        /** Set source */
        $this->setSource($row);

        /** Set restriction domains for product or category source */
        $this->setRestrictionDomains($row);

        /** Logic if source already exists */
        $this->checkIfSourceAlreadyExists();

        /** Set target */
        $this->setTarget($row);

        /** Set deviating domain for product or category redirect */
        $this->setDeviatingDomain($row);

        /** Save redirect and log */
        $this->save();
    }

    public function trimKeysAndValues(array $row): array
    {
        $trimmedRow = [];
        if(empty($row)) {
            return [];
        }

        foreach($row as $rowKey => $rowValue) {
            $rowKey = trim((string) $rowKey);
            $rowValue = trim((string)$rowValue);

            if(!empty($rowKey)) {
                $trimmedRow[$rowKey] = $rowValue;
            }
        }

        return $trimmedRow;
    }

    public function prepareImport(array $row, int $rowIndex): void
    {
        /** Create log entity */
        $this->dreiscSeoRedirectImportExportLogEntity = new DreiscSeoRedirectImportExportLogEntity();
        $this->dreiscSeoRedirectImportExportLogEntity
            ->setRowIndex($rowIndex)
            ->setRowValue($row);

        /** Reset errors */
        $this->errors = [];
    }

    /**
     * @param $row
     */
    private function checkHeader($row): void
    {
        $csvHeaders = array_keys($row);

        /** Abort, if minimum one of the available headers is set */
        foreach(self::AVAILABLE_HEADERS as $availableHeader) {
            if (in_array($availableHeader, $csvHeaders, true)) {
                return;
            }
        }

        throw new \RuntimeException('MISSING_HEADER');
    }

    private function createBaseDreiscSeoRedirectEntity(array $row): DreiscSeoRedirectEntity
    {
        /** Create an entity with default settings */
        $dreiscSeoRedirectEntity = new DreiscSeoRedirectEntity();
        $dreiscSeoRedirectEntity
            ->setId(Uuid::randomHex())
            ->setActive(true)
            ->setRedirectHttpStatusCode(DreiscSeoRedirectEnum::REDIRECT_HTTP_STATUS_CODE__301);

        /** Check for active row */
        if (isset($row[self::HEADER__ACTIVE])) {
            $active = (int) $row[self::HEADER__ACTIVE];
            if (1 !== $active) {
                $dreiscSeoRedirectEntity->setActive(false);
            }
        }

        /** Check for httpStatusCode row */
        if (!empty($row[self::HEADER__HTTP_STATUS_CODE])) {
            if (!in_array($row[self::HEADER__HTTP_STATUS_CODE], DreiscSeoRedirectEnum::VALID__REDIRECT_HTTP_STATUS_CODES, true)) {
                $this->addError(
                    self::ERROR__INVALID_HTTP_STATUS_CODE,
                    [
                        'httpStatusCode' => $row[self::HEADER__HTTP_STATUS_CODE]
                    ]
                );
            } else {
                $dreiscSeoRedirectEntity->setRedirectHttpStatusCode($row[self::HEADER__HTTP_STATUS_CODE]);
            }
        }

        /** Check for parameter forwarding */
        if (isset($row[self::HEADER__PARAMETER_FORWARDING])) {
            $parameterForwarding = (int) $row[self::HEADER__PARAMETER_FORWARDING];
            if (!empty($parameterForwarding)) {
                $dreiscSeoRedirectEntity->setParameterForwarding(true);
            }
        }

        return $dreiscSeoRedirectEntity;
    }

    private function addError(string $error, array $params = []): void
    {
        $this->errors[] = [
            'error' => $error,
            'params' => $params
        ];
    }

    private function setSource(array $row)
    {
        if(!empty($row[self::HEADER__SOURCE_PRODUCT_NUMBER])) {
            $this->setSourceProductNumber($row[self::HEADER__SOURCE_PRODUCT_NUMBER]);
        }


        elseif(!empty($row[self::HEADER__SOURCE_INTERNAL_URL])) {
            $this->setSourceInternalUrl($row[self::HEADER__SOURCE_INTERNAL_URL]);
        }


        elseif(!empty($row[self::HEADER__SOURCE_CATEGORY_ID])) {
            $this->setSourceCategoryId($row[self::HEADER__SOURCE_CATEGORY_ID]);
        }


        elseif(!empty($row[self::HEADER__SOURCE_PRODUCT_ID])) {
            $this->setSourceProductId($row[self::HEADER__SOURCE_PRODUCT_ID]);
        }

        else {
            /** There is no source defined for this row */
            $this->addError(self::ERROR__MISSING_SOURCE);
        }
    }

    private function setSourceProductNumber($sourceProductNumber): void
    {
        /** Try to load the product */
        $productEntity = $this->productRepository->search(
            (new Criteria())->addFilter(
                new EqualsFilter('productNumber', $sourceProductNumber)
            )
        )->first();

        if (null === $productEntity) {
            $this->addError(
                self::ERROR__INVALID_SOURCE_PRODUCT_NUMBER,
                [
                    'productNumber' => $sourceProductNumber
                ]
            );
        } else {
            $this->dreiscSeoRedirectEntity->setSourceType(DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT);
            $this->dreiscSeoRedirectEntity->setSourceProductId($productEntity->getId());
        }
    }

    private function setSourceInternalUrl($sourceInternalUrl): void
    {
        /** Parse the given url */
        $seoUrlParserResultStruct = $this->seoUrlParser->parse($sourceInternalUrl);

        /** Add an error, if the parser failed */
        if (null === $seoUrlParserResultStruct || null === $seoUrlParserResultStruct->getSalesChannelDomainEntity()) {
            $this->addError(
                self::ERROR__INVALID_SOURCE_INTERNAL_URL,
                [
                    'url' => $sourceInternalUrl
                ]
            );
        }

        elseif(empty($seoUrlParserResultStruct->getBaseUrl())) {
            $this->addError(
                self::ERROR__INVALID_SOURCE_INTERNAL_URL_EMPTY_BASE_URL,
                [
                    'url' => $sourceInternalUrl
                ]
            );
        }

        else {
            $this->dreiscSeoRedirectEntity->setSourceType(DreiscSeoRedirectEnum::SOURCE_TYPE__URL);
            $this->dreiscSeoRedirectEntity->setSourceSalesChannelDomainId($seoUrlParserResultStruct->getSalesChannelDomainEntity()->getId());
            $this->dreiscSeoRedirectEntity->setSourcePath($seoUrlParserResultStruct->getBaseUrl());
        }

    }

    private function setSourceCategoryId($sourceCategoryId): void
    {
        $sourceCategoryId = strtolower((string) $sourceCategoryId);

        if (!Uuid::isValid($sourceCategoryId)) {
            $this->addError(
                self::ERROR__INVALID_SOURCE_CATEGORY_ID__UUID_FORMAT,
                [
                    'categoryId' => $sourceCategoryId
                ]
            );

            return;
        }

        /** Try to load the category */
        $categoryEntity = $this->categoryRepository->get($sourceCategoryId);

        if (null === $categoryEntity) {
            $this->addError(
                self::ERROR__INVALID_SOURCE_CATEGORY_ID__NOT_FOUND,
                [
                    'categoryId' => $sourceCategoryId
                ]
            );
        } else {
            $this->dreiscSeoRedirectEntity->setSourceType(DreiscSeoRedirectEnum::SOURCE_TYPE__CATEGORY);
            $this->dreiscSeoRedirectEntity->setSourceCategoryId($sourceCategoryId);
        }
    }

    private function setSourceProductId($sourceProductId): void
    {
        $sourceProductId = strtolower((string) $sourceProductId);

        if (!Uuid::isValid($sourceProductId)) {
            $this->addError(
                self::ERROR__INVALID_SOURCE_PRODUCT_ID__UUID_FORMAT,
                [
                    'productId' => $sourceProductId
                ]
            );

            return;
        }

        /** Try to load the product */
        $productEntity = $this->productRepository->get($sourceProductId);

        if (null === $productEntity) {
            $this->addError(
                self::ERROR__INVALID_SOURCE_PRODUCT_ID__NOT_FOUND,
                [
                    'productId' => $sourceProductId
                ]
            );
        } else {
            $this->dreiscSeoRedirectEntity->setSourceType(DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT);
            $this->dreiscSeoRedirectEntity->setSourceProductId($sourceProductId);
        }
    }

    private function setRestrictionDomains(array $row): void
    {
        /** Abort if source type not product or category */
        if (!in_array($this->dreiscSeoRedirectEntity->getSourceType(), [
            DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT,
            DreiscSeoRedirectEnum::SOURCE_TYPE__CATEGORY
        ], true)) {
            return;
        }

        /** Abort if no restriction domains are set */
        if(empty($row[self::HEADER__SOURCE_RESTRICTION_DOMAINS])) {
            return;
        }

        $restrictionDomainIds = [];
        foreach(explode('|', (string) $row[self::HEADER__SOURCE_RESTRICTION_DOMAINS]) as $restrictionDomain) {
            /** Trim */
            $restrictionDomain = trim($restrictionDomain);

            /** Parse the given domain */
            $salesChannelDomainEntity = $this->fetchSalesChannelDomain($restrictionDomain);

            /** Invalid deviating domain */
            if (null === $salesChannelDomainEntity) {
                $this->addError(
                    self::ERROR__INVALID_RESTRICTION_DOMAIN,
                    [
                        'restrictionDomain' => $restrictionDomain
                    ]
                );

                return;
            }

            $restrictionDomainIds[] = $salesChannelDomainEntity->getId();
        }

        /** Set restriction domain data */
        $this->dreiscSeoRedirectEntity->setHasSourceSalesChannelDomainRestriction(true);
        $this->dreiscSeoRedirectEntity->setSourceSalesChannelDomainRestrictionIds($restrictionDomainIds);
    }

    public function checkIfSourceAlreadyExists(): void
    {
        /** Create a criteria */
        $criteria = new Criteria();

        switch ($this->dreiscSeoRedirectEntity->getSourceType()) {
            case DreiscSeoRedirectEnum::SOURCE_TYPE__URL:

                $criteria->addFilter(
                    new EqualsFilter(
                        'sourceSalesChannelDomainId',
                        $this->dreiscSeoRedirectEntity->getSourceSalesChannelDomainId()
                    ),
                    new EqualsFilter(
                        'sourcePath',
                        $this->dreiscSeoRedirectEntity->getSourcePath()
                    )
                );

                break;

            case DreiscSeoRedirectEnum::SOURCE_TYPE__CATEGORY:

                $criteria->addFilter(
                    new EqualsFilter(
                        'sourceCategoryId',
                        $this->dreiscSeoRedirectEntity->getSourceCategoryId()
                    )
                );

                break;

            case DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT:

                $criteria->addFilter(
                    new EqualsFilter(
                        'sourceProductId',
                        $this->dreiscSeoRedirectEntity->getSourceProductId()
                    )
                );

                break;

            default:
                /** Abort */
                return;
        }

        $duplicates = $this->dreiscSeoRedirectRepository->searchIds($criteria);
        if ($duplicates->getTotal() > 0) {
            $this->addError(
                self::ERROR__SOURCE_ALREADY_EXISTS,
                [
                    'dreiscSeoRedirectId' => $duplicates->firstId()
                ]
            );
        }
    }

    private function setTarget(array $row)
    {
        if(!empty($row[self::HEADER__TARGET_PRODUCT_NUMBER])) {
            $this->setTargetProductNumber($row[self::HEADER__TARGET_PRODUCT_NUMBER]);
        }

        elseif(!empty($row[self::HEADER__TARGET_CATEGORY_ID])) {
            $this->setTargetCategoryId($row[self::HEADER__TARGET_CATEGORY_ID]);
        }

        elseif(!empty($row[self::HEADER__TARGET_PRODUCT_ID])) {
            $this->setTargetProductId($row[self::HEADER__TARGET_PRODUCT_ID]);
        }

        elseif(!empty($row[self::HEADER__TARGET_INTERNAL_URL])) {
            $this->setTargetInternalUrl($row[self::HEADER__TARGET_INTERNAL_URL]);
        }

        elseif(!empty($row[self::HEADER__TARGET_EXTERNAL_URL])) {
            $this->setTargetExternalUrl($row[self::HEADER__TARGET_EXTERNAL_URL]);
        }

        else {
            /** There is no target defined for this row */
            $this->addError(self::ERROR__MISSING_TARGET);
        }
    }

    private function setTargetProductNumber($targetProductNumber): void
    {
        /** Try to load the product */
        $productEntity = $this->productRepository->search(
            (new Criteria())->addFilter(
                new EqualsFilter('productNumber', $targetProductNumber)
            )
        )->first();

        if (null === $productEntity) {
            $this->addError(
                self::ERROR__INVALID_TARGET_PRODUCT_NUMBER,
                [
                    'productNumber' => $targetProductNumber
                ]
            );
        } else {
            $this->dreiscSeoRedirectEntity->setRedirectType(DreiscSeoRedirectEnum::REDIRECT_TYPE__PRODUCT);
            $this->dreiscSeoRedirectEntity->setRedirectProductId($productEntity->getId());
        }
    }

    private function setTargetCategoryId($targetCategoryId): void
    {
        $targetCategoryId = strtolower((string) $targetCategoryId);

        if (!Uuid::isValid($targetCategoryId)) {
            $this->addError(
                self::ERROR__INVALID_TARGET_CATEGORY_ID__UUID_FORMAT,
                [
                    'categoryId' => $targetCategoryId
                ]
            );

            return;
        }

        /** Try to load the product */
        $categoryEntity = $this->categoryRepository->get($targetCategoryId);

        if (null === $categoryEntity) {
            $this->addError(
                self::ERROR__INVALID_TARGET_CATEGORY_ID__NOT_FOUND,
                [
                    'categoryId' => $targetCategoryId
                ]
            );
        } else {
            $this->dreiscSeoRedirectEntity->setRedirectType(DreiscSeoRedirectEnum::REDIRECT_TYPE__CATEGORY);
            $this->dreiscSeoRedirectEntity->setRedirectCategoryId($targetCategoryId);
        }
    }

    private function setTargetProductId($targetProductId): void
    {
        $targetProductId = strtolower((string) $targetProductId);

        if (!Uuid::isValid($targetProductId)) {
            $this->addError(
                self::ERROR__INVALID_TARGET_PRODUCT_ID__UUID_FORMAT,
                [
                    'productId' => $targetProductId
                ]
            );

            return;
        }

        /** Try to load the product */
        $productEntity = $this->productRepository->get($targetProductId);

        if (null === $productEntity) {
            $this->addError(
                self::ERROR__INVALID_TARGET_PRODUCT_ID__NOT_FOUND,
                [
                    'productId' => $targetProductId
                ]
            );
        } else {
            $this->dreiscSeoRedirectEntity->setRedirectType(DreiscSeoRedirectEnum::REDIRECT_TYPE__PRODUCT);
            $this->dreiscSeoRedirectEntity->setRedirectProductId($targetProductId);
        }
    }

    private function setTargetInternalUrl($targetInternalUrl): void
    {
        /** Parse the given url */
        $seoUrlParserResultStruct = $this->seoUrlParser->parse($targetInternalUrl);

        /** Add an error, if the parser failed */
        if (null === $seoUrlParserResultStruct || null === $seoUrlParserResultStruct->getSalesChannelDomainEntity()) {
            $this->addError(
                self::ERROR__INVALID_TARGET_INTERNAL_URL,
                [
                    'url' => $targetInternalUrl
                ]
            );
        }

        else {
            $this->dreiscSeoRedirectEntity->setRedirectType(DreiscSeoRedirectEnum::REDIRECT_TYPE__URL);
            $this->dreiscSeoRedirectEntity->setRedirectSalesChannelDomainId($seoUrlParserResultStruct->getSalesChannelDomainEntity()->getId());
            $this->dreiscSeoRedirectEntity->setRedirectPath($seoUrlParserResultStruct->getBaseUrl());
        }

    }

    private function setTargetExternalUrl($targetExternalUrl): void
    {
        $this->dreiscSeoRedirectEntity->setRedirectType(DreiscSeoRedirectEnum::REDIRECT_TYPE__EXTERNAL_URL);
        $this->dreiscSeoRedirectEntity->setRedirectUrl($targetExternalUrl);
    }

    private function setDeviatingDomain(array $row)
    {
        /** Abort is target type not product or category */
        if (!in_array($this->dreiscSeoRedirectEntity->getRedirectType(), [
            DreiscSeoRedirectEnum::REDIRECT_TYPE__PRODUCT,
            DreiscSeoRedirectEnum::REDIRECT_TYPE__CATEGORY
        ], true)) {
            return;
        }

        /** Abort if no deviating domain is set */
        if(empty($row[self::HEADER__TARGET_DEVIATING_DOMAIN])) {
            return;
        }

        /** Parse the given domain */
        $salesChannelDomainEntity = $this->fetchSalesChannelDomain($row[self::HEADER__TARGET_DEVIATING_DOMAIN]);

        /** Invalid deviating domain */
        if (null === $salesChannelDomainEntity) {
            $this->addError(
                self::ERROR__INVALID_DEVIATING_DOMAIN,
                [
                    'deviatingDomain' => $row[self::HEADER__TARGET_DEVIATING_DOMAIN]
                ]
            );

            return;
        }

        /** Set deviating domain data */
        $this->dreiscSeoRedirectEntity->setHasDeviatingRedirectSalesChannelDomain(true);
        $this->dreiscSeoRedirectEntity->setDeviatingRedirectSalesChannelDomainId(
            $salesChannelDomainEntity->getId()
        );
    }

    private function save()
    {
        /** Save the redirect, if there are no errors */
        if(empty($this->errors)) {
            $this->dreiscSeoRedirectRepository->create([
                $this->dreiscSeoRedirectEntity
            ]);

            $this->dreiscSeoRedirectImportExportLogEntity->setDreiscSeoRedirectId(
                $this->dreiscSeoRedirectEntity->getId()
            );
        } else {
            $this->dreiscSeoRedirectImportExportLogEntity->setErrors($this->errors);
        }

        $this->dreiscSeoRedirectImportExportLogRepository->create([
            $this->dreiscSeoRedirectImportExportLogEntity
        ]);
    }

    /**
     * @param $domain
     */
    private function fetchSalesChannelDomain($domain): ?SalesChannelDomainEntity
    {
        $salesChannelDomainSearchResult = $this->salesChannelDomainRepository->search(
            (new Criteria())->addFilter(
                new EqualsFilter('url', $domain)
            )
        );

        return $salesChannelDomainSearchResult->first();
    }
}
