<?php declare(strict_types=1);

namespace Acris\Gpsr\Components\ProductGpsrInfo;

use Acris\Gpsr\Components\ProductGpsrInfo\Struct\GpsrInfoCollection;
use Acris\Gpsr\Components\ProductGpsrInfo\Struct\GpsrInfoNoteStruct;
use Acris\Gpsr\Components\ProductGpsrInfo\Struct\GpsrInfoStruct;
use Acris\Gpsr\Components\ProductGpsrInfo\Struct\GpsrMasterStruct;
use Acris\Gpsr\Custom\AbstractGpsrModuleEntity;
use Acris\Gpsr\Custom\GpsrNoteEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Util\Hasher;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;

class GpsrService
{
    public const ACRIS_GPSR_EXTENSION_KEY = 'acrisGpsr';

    private $fileNames = [];
    private array $cachedGpsrMasterStructs;

    public function __construct(private readonly GpsrGateway $gpsrGateway,
                                private readonly SystemConfigService $configService,
                                private readonly EntityRepository $mediaRepository)
    {
        $this->cachedGpsrMasterStructs = [];
    }

    public function loadGpsr(SalesChannelProductEntity $product, SalesChannelContext $context): void
    {
        // Inheritance of fields
        // types
        //// from product
        // manufacturers
        //// from module, product or manufacturer
        // contacts
        //// from module, product or manufacturer
        // hints
        //// from module or product (3 fields)

        // part 1: build inheritance
        $gpsrMasterStruct = new GpsrMasterStruct();
        // prio 1  get from module
        $this->collectFromModule($gpsrMasterStruct, $product, $context);
        // prio 2 - get from product
        $this->collectFromProduct($gpsrMasterStruct, $product, $context);
        // prio 3 - get from product manufacturer
        $this->collectFromManufacturer($gpsrMasterStruct, $product, $context);

        // part 2: filter by displayType and position and sort by priority - is done in twig on get collection by displayType and position
        $product->addExtension(self::ACRIS_GPSR_EXTENSION_KEY, $gpsrMasterStruct);
    }

    public function collectFromModule(GpsrMasterStruct $gpsrMasterStruct, SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): void
    {
        if (empty($product->getStreams()) && empty($product->getStreams()->getIds())) {
            return;
        }

        $streamIds = $product->getStreams()->getIds();
        sort($streamIds); // Sortiere die IDs
        $productStreamHash = Hasher::hash($streamIds);

        if (isset($this->cachedGpsrMasterStructs[$productStreamHash])) {
            $cachedGpsrMasterStruct = $this->cachedGpsrMasterStructs[$productStreamHash];
            $gpsrMasterStruct->setManufacturers($cachedGpsrMasterStruct->getManufacturers());
            $gpsrMasterStruct->setContacts($cachedGpsrMasterStruct->getContacts());
            $gpsrMasterStruct->setNotes($cachedGpsrMasterStruct->getNotes());
        } else {
            $gpsrMasterStruct->setManufacturers($this->convertGpsrInfoStruct(GpsrInfoStruct::CONTENT_TYPE_MANUFACTURER, $this->gpsrGateway->getGpsrManufacturersInfoFromDB($product->getStreams()->getIds(), $salesChannelContext)->getEntities(), $salesChannelContext));
            $gpsrMasterStruct->setContacts($this->convertGpsrInfoStruct(GpsrInfoStruct::CONTENT_TYPE_CONTACT, $this->gpsrGateway->getGpsrContactsInfoFromDB($product->getStreams()->getIds(), $salesChannelContext)->getEntities(), $salesChannelContext));
            $gpsrMasterStruct->setNotes($this->convertGpsrNotesInfoStruct(GpsrInfoStruct::CONTENT_TYPE_NOTE, $this->gpsrGateway->getGpsrNotesInfoFromDB($product->getStreams()->getIds(), $salesChannelContext)->getEntities(), $salesChannelContext));
            $this->cachedGpsrMasterStructs[$productStreamHash] = $gpsrMasterStruct;
        }
    }

    public function collectFromProduct(GpsrMasterStruct $gpsrMasterStruct, SalesChannelProductEntity $salesChannelProduct, SalesChannelContext $salesChannelContext)
    {
        $isHiddenType = $this->configService->get('AcrisGpsrCS.config.productTypeDisplay', $salesChannelContext->getSalesChannelId()) === 'noDisplay';

        if(!$isHiddenType) {
            $this->convertProductGpsrInfo($gpsrMasterStruct->getTypes(), GpsrInfoStruct::CONTENT_TYPE_TYPE, 'type', 'productTypeDisplay', $salesChannelProduct, $salesChannelContext);
        }

        $isHiddenManufacturer = $this->configService->get('AcrisGpsrCS.config.productManuDetailDisplay', $salesChannelContext->getSalesChannelId()) === 'noDisplay';

        if($gpsrMasterStruct->getManufacturers()->count() === 0 && !$isHiddenManufacturer ) {
           $this->convertProductGpsrInfo($gpsrMasterStruct->getManufacturers(), GpsrInfoStruct::CONTENT_TYPE_MANUFACTURER, 'manufacturer', 'productManuDetailDisplay', $salesChannelProduct, $salesChannelContext);
        }

        $isHiddenPerson = $this->configService->get('AcrisGpsrCS.config.productPersonDisplay', $salesChannelContext->getSalesChannelId()) === 'noDisplay';

        if($gpsrMasterStruct->getContacts()->count() === 0 && !$isHiddenPerson) {
            $this->convertProductGpsrInfo($gpsrMasterStruct->getContacts(), GpsrInfoStruct::CONTENT_TYPE_CONTACT, 'contact', 'productPersonDisplay', $salesChannelProduct, $salesChannelContext);
        }
        if($gpsrMasterStruct->getNotes()->count() === 0) {
            $this->convertProductGpsrNoteInfo($gpsrMasterStruct->getNotes(), GpsrInfoStruct::CONTENT_TYPE_NOTE, GpsrInfoNoteStruct::NOTE_TYPE_WARNING, 'hint_warning', 'productWarningDisplay', $salesChannelProduct, $salesChannelContext);
            $this->convertProductGpsrNoteInfo($gpsrMasterStruct->getNotes(), GpsrInfoStruct::CONTENT_TYPE_NOTE, GpsrInfoNoteStruct::NOTE_TYPE_SECURITY, 'hint_safety', 'productWarningDisplay', $salesChannelProduct, $salesChannelContext);
            $this->convertProductGpsrNoteInfo($gpsrMasterStruct->getNotes(), GpsrInfoStruct::CONTENT_TYPE_NOTE, GpsrInfoNoteStruct::NOTE_TYPE_INFORMATION, 'hint_information', 'productWarningDisplay', $salesChannelProduct, $salesChannelContext);
        }
    }

    public function collectFromManufacturer(GpsrMasterStruct $gpsrMasterStruct, SalesChannelProductEntity $salesChannelProduct, SalesChannelContext $salesChannelContext)
    {
        $manufacturer = $salesChannelProduct->getManufacturer();
        if (!$manufacturer instanceof ProductManufacturerEntity) {
            return;
        }

        if($gpsrMasterStruct->getManufacturers()->count() === 0) {
            $this->convertManufacturerGpsrInfo($gpsrMasterStruct->getManufacturers(), GpsrInfoStruct::CONTENT_TYPE_MANUFACTURER, 'acris_gpsr_manufacturer', 'manuDetailDisplay', $salesChannelProduct->getManufacturer(), $salesChannelProduct, $salesChannelContext);
        }
        if($gpsrMasterStruct->getContacts()->count() === 0) {
            $this->convertManufacturerGpsrInfo($gpsrMasterStruct->getContacts(), GpsrInfoStruct::CONTENT_TYPE_CONTACT, 'acris_gpsr_contact', 'manuPersonDisplay', $salesChannelProduct->getManufacturer(), $salesChannelProduct, $salesChannelContext);
        }
    }

    private function convertGpsrInfoStruct(string $contentType, EntityCollection $gpsrEntityCollection, SalesChannelContext $salesChannelContext): GpsrInfoCollection
    {
        $gpsrInfoCollection = new GpsrInfoCollection();
        /** @var AbstractGpsrModuleEntity $gpsrEntity */
        foreach ($gpsrEntityCollection->getElements() as $gpsrEntity) {
            $gpsrInfoStruct = new GpsrInfoStruct();
            $downloadDocuments = $gpsrEntity->getExtension($this->resolveExtensionName($contentType));
            $documentsUrls = [];
            $mediaIds = [];
            if($downloadDocuments) {
                foreach ($downloadDocuments as $document) {
                    $this->fileNames[$document->getMediaId()] = $document->getFileName();;
                    $mediaIds[] = $document->getMediaId();
                }
                $documentsUrls = $this->getFileUrls($mediaIds, $salesChannelContext->getContext());

            }
            $gpsrInfoStruct->setContentType($contentType);
            $this->convertBasicGpsrInfoStruct($gpsrInfoStruct, $gpsrEntity, $salesChannelContext);
            $gpsrInfoStruct->setDocumentsUrls($documentsUrls);
            $gpsrInfoCollection->add($gpsrInfoStruct);
        }
        return $gpsrInfoCollection;
    }

    private function resolveExtensionName(string $contentType)
    {
        if($contentType === GpsrInfoStruct::CONTENT_TYPE_MANUFACTURER) {
            return "acrisGpsrManufacturerDownloads";
        }

        if($contentType === GpsrInfoStruct::CONTENT_TYPE_CONTACT) {
            return "acrisGpsrContactDownloads";
        }

        if($contentType === GpsrInfoStruct::CONTENT_TYPE_NOTE) {
            return "acrisGpsrNoteDownloads";
        }

        return '';
    }

    private function convertGpsrNotesInfoStruct(string $contentType, EntityCollection $gpsrEntityCollection, SalesChannelContext $salesChannelContext): GpsrInfoCollection
    {
        $gpsrInfoCollection = new GpsrInfoCollection();
        /** @var GpsrNoteEntity $gpsrEntity */
        foreach ($gpsrEntityCollection->getElements() as $gpsrEntity) {
            $documents = [];
            $mediaIds = [];

            if($gpsrEntity->getExtension("acrisGpsrNoteDownloads")) {
                foreach ($gpsrEntity->getExtension("acrisGpsrNoteDownloads") as $document) {
                    $mediaIds[] = $document->getMediaId();
                    $this->fileNames[$document->getMediaId()] = $document->getFileName();;

                }
            }

            if($mediaIds) {
                $documents = $this->getFileUrls($mediaIds, $salesChannelContext->getContext());
            }


            $gpsrInfoStruct = new GpsrInfoNoteStruct();
            $gpsrInfoStruct->setContentType($contentType);
            $this->convertBasicGpsrInfoStruct($gpsrInfoStruct, $gpsrEntity, $salesChannelContext);

            $gpsrInfoStruct->setNoteType($gpsrEntity->getNoteType());
            $gpsrInfoStruct->setBackgroundColor($gpsrEntity->getBackgroundColor());
            $gpsrInfoStruct->setBorderColor($gpsrEntity->getBorderColor());
            $gpsrInfoStruct->setHeadlineColor($gpsrEntity->getHeadlineColor());
            $gpsrInfoStruct->setHintHeadlineSeoSize($gpsrEntity->getHintHeadlineSeoSize());
            $gpsrInfoStruct->setHintAlignment($gpsrEntity->getHintAlignment());
            $gpsrInfoStruct->setHintHeadlineColor($gpsrEntity->getHintHeadlineColor());
            $gpsrInfoStruct->setHintEnableHeadlineSize($gpsrEntity->getHintEnableHeadlineSize());
            $gpsrInfoStruct->setMediaPosition($gpsrEntity->getMediaPosition());
            $gpsrInfoStruct->setMediaSize($gpsrEntity->getMediaSize());
            $gpsrInfoStruct->setMobileVisibility($gpsrEntity->getMobileVisibility());
            $gpsrInfoStruct->setMediaId($gpsrEntity->getMediaId());
            $gpsrInfoStruct->setMedia($gpsrEntity->getMedia());
            $gpsrInfoStruct->setDocumentsUrls($documents);
            $gpsrInfoCollection->add($gpsrInfoStruct);
        }

        return $gpsrInfoCollection;
    }

    private function getFileUrls(array $mediaIds,Context $context): array
    {
        if(empty($mediaIds)) {
            return [];
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', $mediaIds));
        $mediaEntities = $this->mediaRepository->search($criteria, $context)->getEntities();

        $urls = [];
        foreach ($mediaEntities as $media) {
            $urls[] = ['url' =>$media->getUrl(),'filename'=>$this->resolveFileName($media)];
        }

        return $urls;
    }

    private function resolveFileName(MediaEntity $media) : string
    {
        $fileName = $this->fileNames[$media->getId()] ?? null;

        if($fileName) {
            return $fileName;
        }

        return $media->getFileName(). '.' . $media->getFileExtension();
    }

    private function convertBasicGpsrInfoStruct(GpsrInfoStruct $gpsrInfoStruct, AbstractGpsrModuleEntity $gpsrEntity, SalesChannelContext $salesChannelContext): void
    {
        $gpsrInfoStruct->setDisplayType($gpsrEntity->getDisplayType());
        if($gpsrEntity->getDisplayType() === GpsrInfoStruct::DISPLAY_TYPE_GPSR_TAB) {
            $gpsrInfoStruct->setTabPosition($this->configService->get('AcrisGpsrCS.config.gpsrTabPosition', $salesChannelContext->getSalesChannelId()));
        } else {
            $gpsrInfoStruct->setTabPosition($gpsrEntity->getTabPosition());
        }
        $gpsrInfoStruct->setDescriptionDisplay($gpsrEntity->getDescriptionDisplay());
        $gpsrInfoStruct->setDescriptionPosition($gpsrEntity->getDescriptionPosition());
        $gpsrInfoStruct->setDisplaySeparator($gpsrEntity->getDisplaySeparator());
        $gpsrInfoStruct->setPriority($gpsrEntity->getPriority());

        $gpsrInfoStruct->setHeadline($gpsrEntity->getTranslation('headline'));
        $gpsrInfoStruct->setText($gpsrEntity->getTranslation('text'));

        $gpsrInfoStruct->setName($gpsrEntity->getTranslation('name'));
        $gpsrInfoStruct->setStreet($gpsrEntity->getTranslation('street'));
        $gpsrInfoStruct->setHouseNumber($gpsrEntity->getTranslation('houseNumber'));
        $gpsrInfoStruct->setZipcode($gpsrEntity->getTranslation('zipcode'));
        $gpsrInfoStruct->setCity($gpsrEntity->getTranslation('city'));
        $gpsrInfoStruct->setCountry($gpsrEntity->getTranslation('country'));
        $gpsrInfoStruct->setPhoneNumber($gpsrEntity->getTranslation('phoneNumber'));
        $gpsrInfoStruct->setAddress($gpsrEntity->getTranslation('address'));

        $gpsrInfoStruct->setModalInfoText($gpsrEntity->getTranslation('modalInfoText'));
        $gpsrInfoStruct->setModalLinkText($gpsrEntity->getTranslation('modalLinkText'));

        $gpsrInfoStruct->updateHasAddress();
    }

    private function convertProductGpsrInfo(GpsrInfoCollection $gpsrInfoCollection, string $contentType, string $type, string $configDisplayType, SalesChannelProductEntity $salesChannelProduct, SalesChannelContext $salesChannelContext): void
    {
        if (!empty($salesChannelProduct->getTranslation('customFields')) && isset($salesChannelProduct->getTranslation('customFields')['acris_gpsr_product_' . $type])) {
            $gpsrInfoStruct = new GpsrInfoStruct();
            $this->setProductGpsrBasicInfo($gpsrInfoStruct, $contentType, $type, $configDisplayType, $salesChannelProduct, $salesChannelContext);
            $gpsrInfoCollection->add($gpsrInfoStruct);
        }
    }

    private function convertProductGpsrNoteInfo(GpsrInfoCollection $gpsrInfoCollection, string $contentType, string $noteType, string $type, string $configDisplayType, SalesChannelProductEntity $salesChannelProduct, SalesChannelContext $salesChannelContext): void
    {
        if (!empty($salesChannelProduct->getTranslation('customFields')) && isset($salesChannelProduct->getTranslation('customFields')['acris_gpsr_product_' . $type])
        || $salesChannelProduct->getExtension("acrisGpsrDownloads")) {
            $gpsrInfoStruct = new GpsrInfoNoteStruct();
            $gpsrInfoStruct->setNoteType($noteType);
            $this->setProductGpsrBasicInfo($gpsrInfoStruct, $contentType, $type, $configDisplayType, $salesChannelProduct, $salesChannelContext,$noteType);
            $text = $gpsrInfoStruct->getText();

            if($text || !empty($gpsrInfoStruct->getDocumentUrls())) {
                $gpsrInfoCollection->add($gpsrInfoStruct);
            }
        }
    }

    private function setProductGpsrBasicInfo(GpsrInfoStruct $gpsrInfoStruct, string $contentType, string $type, string $configDisplayType, SalesChannelProductEntity $salesChannelProduct, SalesChannelContext $salesChannelContext,string $noteType = "")
    {
        $gpsrInfoStruct->setContentType($contentType);
        $displayType = $this->configService->get('AcrisGpsrCS.config.' . $configDisplayType, $salesChannelContext->getSalesChannelId());
        if($displayType === 'noDisplay') {
            return;
        }
        if($displayType === 'ownTab') {
            $gpsrInfoStruct->setDisplayType(GpsrInfoStruct::DISPLAY_TYPE_TAB);
        } elseif($displayType === 'gpsrTab') {
            $gpsrInfoStruct->setDisplayType(GpsrInfoStruct::DISPLAY_TYPE_GPSR_TAB);
            $gpsrInfoStruct->setTabPosition($this->configService->get('AcrisGpsrCS.config.gpsrTabPosition', $salesChannelContext->getSalesChannelId()));
        }

        if($salesChannelProduct->getExtension("acrisGpsrDownloads")) {
            $documents = [];
            foreach ($salesChannelProduct->getExtension("acrisGpsrDownloads") as $document) {
                if($document->getGpsrType() === "warning_note" && $noteType === 'warning') {
                    $documents[] = $document->getMediaId();
                    $this->fileNames[$document->getMediaId()] = $document->getFileName();
                }

                if($document->getGpsrType() === "security_note" && $noteType === 'security') {
                    $documents[] = $document->getMediaId();
                    $this->fileNames[$document->getMediaId()] = $document->getFileName();
                }

                if($document->getGpsrType() === "important_info" && $noteType === 'information') {
                    $documents[] = $document->getMediaId();
                    $this->fileNames[$document->getMediaId()] = $document->getFileName();
                }
            }
            $gpsrInfoStruct->setDocumentsUrls($this->getFileUrls($documents, $salesChannelContext->getContext()));
        }
        if(isset($salesChannelProduct->getTranslation('customFields')['acris_gpsr_product_' . $type])) {
            $gpsrInfoStruct->setText($salesChannelProduct->getTranslation('customFields')['acris_gpsr_product_' . $type]);
        }
    }

    private function convertManufacturerGpsrInfo(GpsrInfoCollection $gpsrInfoCollection, string $contentType, string $customFieldPrefix, string $configDisplayType, ProductManufacturerEntity $manufacturer, SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): void
    {
        $manufacturerExtension = $manufacturer->getExtension("acrisManufacturerDownloads");
        $documents = [];
        $documentsUrls = [];

        $customFieldSuffix = '';

        $customFields = $manufacturer->getTranslation('customFields');
        if ($this->configService->get('AcrisGpsrCS.config.displayERPImportedManufacturerGPSRData', $salesChannelContext->getSalesChannelId()) === true) {
            $customFieldSuffix = '_erp';
            $customFields = $product->getTranslation('customFields') ?? [];
        }

        if($manufacturerExtension && $configDisplayType === 'manuDetailDisplay') {
            foreach ($manufacturerExtension as $document) {
                $documents[] = $document->getMediaId();
                $this->fileNames[$document->getMediaId()] = $document->getFileName();;
            }
            $documentsUrls = $this->getFileUrls($documents, $salesChannelContext->getContext());

        }

        if(empty($customFields)) {

            if(!$documentsUrls) {
                return;
            }

            $gpsrInfoStruct = new GpsrInfoStruct();
            $gpsrInfoStruct->setDocumentsUrls($documentsUrls);
            $gpsrInfoCollection->add($gpsrInfoStruct);
            return;
        }

        $displayType = $this->configService->get('AcrisGpsrCS.config.' . $configDisplayType, $salesChannelContext->getSalesChannelId());
        if($displayType === 'noDisplay') {
            return;
        }
        $gpsrInfoStruct = new GpsrInfoStruct();
        $gpsrInfoStruct->setDocumentsUrls($documentsUrls);
        $gpsrInfoStruct->setContentType($contentType);
        if($displayType === 'ownTab') {
            $gpsrInfoStruct->setDisplayType(GpsrInfoStruct::DISPLAY_TYPE_TAB);
        } elseif($displayType === 'gpsrTab') {
            $gpsrInfoStruct->setDisplayType(GpsrInfoStruct::DISPLAY_TYPE_GPSR_TAB);
            $gpsrInfoStruct->setTabPosition($this->configService->get('AcrisGpsrCS.config.gpsrTabPosition', $salesChannelContext->getSalesChannelId()));
        }
        $gpsrInfoStruct->setName($customFields[$customFieldPrefix . '_name' . $customFieldSuffix] ?? null);
        $gpsrInfoStruct->setStreet($customFields[$customFieldPrefix . '_street' . $customFieldSuffix] ?? null);
        $gpsrInfoStruct->setHouseNumber($customFields[$customFieldPrefix . '_house_number' . $customFieldSuffix] ?? null);
        $gpsrInfoStruct->setCity($customFields[$customFieldPrefix . '_city' . $customFieldSuffix] ?? null);
        $gpsrInfoStruct->setZipcode($customFields[$customFieldPrefix . '_zipcode' . $customFieldSuffix] ?? null);
        $gpsrInfoStruct->setCountry($customFields[$customFieldPrefix . '_country' . $customFieldSuffix] ?? null);
        $gpsrInfoStruct->setPhoneNumber($customFields[$customFieldPrefix . '_phone_number' . $customFieldSuffix] ?? null);
        $gpsrInfoStruct->setAddress($customFields[$customFieldPrefix . '_address' . $customFieldSuffix] ?? null);

        /**
         * Display JTL WaWi custom fields if the ACRIS ERP fields are not used
         * #32287
         */
        if ($this->configService->get('AcrisGpsrCS.config.displayERPImportedManufacturerGPSRData', $salesChannelContext->getSalesChannelId()) === true && $customFieldPrefix == "acris_gpsr_manufacturer") {
            if(!isset($customFields['acris_gpsr_manufacturer_name_erp'])) {
                $gpsrInfoStruct->setName($customFields['gpsr_manufacturer_name'] ?? null);
            }
            if(!isset($customFields['acris_gpsr_manufacturer_street_erp'])) {
                $gpsrInfoStruct->setStreet($customFields['gpsr_manufacturer_street'] ?? null);
            }
            if(!isset($customFields['acris_gpsr_manufacturer_house_number_erp'])) {
                $gpsrInfoStruct->setHouseNumber($customFields['gpsr_manufacturer_housenumber'] ?? null);
            }
            if(!isset($customFields['acris_gpsr_manufacturer_city_erp'])) {
                $gpsrInfoStruct->setCity($customFields['gpsr_manufacturer_city'] ?? null);
            }
            if(!isset($customFields['acris_gpsr_manufacturer_zipcode_erp'])) {
                $gpsrInfoStruct->setZipcode($customFields['gpsr_manufacturer_postalcode'] ?? null);
            }
            if(!isset($customFields['acris_gpsr_manufacturer_country_erp'])) {
                $gpsrInfoStruct->setCountry($customFields['gpsr_manufacturer_country'] ?? null);
            }
            if(!isset($customFields['acris_gpsr_manufacturer_address_erp']) && isset($customFields['gpsr_manufacturer_email']) && isset($customFields['gpsr_manufacturer_homepage'])) {
                $address = $customFields['gpsr_manufacturer_email'] . " | " . $customFields['gpsr_manufacturer_homepage'];
                $gpsrInfoStruct->setAddress($address);
            }
        }

        $gpsrInfoStruct->updateHasAddress();
        if($gpsrInfoStruct->isHasAddress()) {
            $gpsrInfoCollection->add($gpsrInfoStruct);
        }
    }
}