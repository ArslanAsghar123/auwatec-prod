<?php declare(strict_types=1);

namespace Acris\Gpsr\Storefront\Subscriber;

use Acris\Gpsr\Custom\ProductGpsrDownloadCollection;
use Acris\Gpsr\Custom\ProductGpsrDownloadEntity;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\ImportExport\Event\ImportExportBeforeExportRecordEvent;
use Shopware\Core\Content\ImportExport\Event\ImportExportBeforeImportRecordEvent;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[Package('storefront')]
class ImportExportSubscriber implements EventSubscriberInterface
{
    public const DEFAULT_SOURCE_ENTITY_PRODUCT = 'product';
    public const DEFAULT_SOURCE_ENTITY_MANUFACTURER = 'product_manufacturer';
    public const DEFAULT_MANUFACTURER_SHOPWARE_LINK_OPTIONS = [
        'standard',
        'modal'
    ];
    public const DEFAULT_SOURCE_ENTITY_KEY = 'sourceEntity';

    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $productManufacturerRepository,
        private readonly EntityRepository $mediaRepository,
        private readonly EntityRepository $gpsrProductDownloadRepository,
        private readonly Connection $connection
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            ImportExportBeforeImportRecordEvent::class => 'onImportExportBeforeImportRecord',
            ImportExportBeforeExportRecordEvent::class => 'onImportExportBeforeExportRecord',
        ];
    }

    public function onImportExportBeforeImportRecord(ImportExportBeforeImportRecordEvent $event): void
    {
        $config = $event->getConfig();

        if ($config->get(self::DEFAULT_SOURCE_ENTITY_KEY) === self::DEFAULT_SOURCE_ENTITY_PRODUCT) {
            $data = $event->getRow();

            if ($this->skipProductRow($data) === true) {
                return;
            }

            $event->setRecord($this->buildImportProductRecord($event->getRecord(), $data, $event->getContext()));
        }

        if ($config->get(self::DEFAULT_SOURCE_ENTITY_KEY) === self::DEFAULT_SOURCE_ENTITY_MANUFACTURER) {
            $data = $event->getRow();

            if ($this->skipManufacturerRow($data) === true) {
                return;
            }

            $event->setRecord($this->buildImportManufacturerRecord($event->getRecord(), $data, $event->getContext()));
        }
    }

    public function onImportExportBeforeExportRecord(ImportExportBeforeExportRecordEvent $event): void
    {
        $config = $event->getConfig();
        
        if ($config->get(self::DEFAULT_SOURCE_ENTITY_KEY) === self::DEFAULT_SOURCE_ENTITY_PRODUCT) {
            $record = $event->getOriginalRecord();

            if (empty($record) || !array_key_exists('id', $record) || empty($record['id'])) {
                return;
            }

            $event->setRecord($this->buildExportProductRecord($event->getRecord(), $record));
        }

        if ($config->get(self::DEFAULT_SOURCE_ENTITY_KEY) === self::DEFAULT_SOURCE_ENTITY_MANUFACTURER) {
            $record = $event->getOriginalRecord();

            if (empty($record) || !array_key_exists('name', $record) || empty($record['name'])) {
                return;
            }

            $event->setRecord($this->buildExportManufacturerRecord($event->getRecord(), $record));
        }
    }

    private function buildExportProductRecord(array $record, array $product): array
    {
        $customFields = $product['customFields'] ?? [];
        $type = array_key_exists('acris_gpsr_product_type', $customFields) && !empty($customFields['acris_gpsr_product_type']) ? $customFields['acris_gpsr_product_type']: '';
        $manufacturer = array_key_exists('acris_gpsr_product_manufacturer', $customFields) && !empty($customFields['acris_gpsr_product_manufacturer']) ? $customFields['acris_gpsr_product_manufacturer']: '';
        $responsiblePerson = array_key_exists('acris_gpsr_product_contact', $customFields) && !empty($customFields['acris_gpsr_product_contact']) ? $customFields['acris_gpsr_product_contact']: '';
        $warningNote = array_key_exists('acris_gpsr_product_hint_warning', $customFields) && !empty($customFields['acris_gpsr_product_hint_warning']) ? $customFields['acris_gpsr_product_hint_warning']: '';
        $safetyNote = array_key_exists('acris_gpsr_product_hint_safety', $customFields) && !empty($customFields['acris_gpsr_product_hint_safety']) ? $customFields['acris_gpsr_product_hint_safety']: '';
        $importantInformation = array_key_exists('acris_gpsr_product_hint_information', $customFields) && !empty($customFields['acris_gpsr_product_hint_information']) ? $customFields['acris_gpsr_product_hint_information']: '';
        $documentsReplacement = array_key_exists('acris_gpsr_product_replace_documents_mode', $customFields) && $customFields['acris_gpsr_product_replace_documents_mode'] === true ? '1' : '0';
        $downloads = array_key_exists('extensions', $product) && !empty($product['extensions']) && is_array($product['extensions']) && array_key_exists('acrisGpsrDownloads', $product['extensions']) && !empty($product['extensions']['acrisGpsrDownloads']) && $product['extensions']['acrisGpsrDownloads'] instanceof ProductGpsrDownloadCollection ? $product['extensions']['acrisGpsrDownloads'] : null;
        list($warningNoteFiles, $safetyNoteFiles, $importantInformationFiles) = $this->getDownloadFiles($downloads);

        if (array_key_exists('GPSRType', $record) && empty($record['GPSRType']) && !empty($type)) {
            $record['GPSRType'] = $type;
        }

        if (array_key_exists('GPSRManufacturer', $record) && empty($record['GPSRManufacturer']) && !empty($manufacturer)) {
            $record['GPSRManufacturer'] = $manufacturer;
        }

        if (array_key_exists('GPSRResponsiblePerson', $record) && empty($record['GPSRResponsiblePerson']) && !empty($responsiblePerson)) {
            $record['GPSRResponsiblePerson'] = $responsiblePerson;
        }

        if (array_key_exists('GPSRWarningNote', $record) && empty($record['GPSRWarningNote']) && !empty($warningNote)) {
            $record['GPSRWarningNote'] = $warningNote;
        }

        if (array_key_exists('GPSRSafetyNote', $record) && empty($record['GPSRSafetyNote']) && !empty($safetyNote)) {
            $record['GPSRSafetyNote'] = $safetyNote;
        }

        if (array_key_exists('GPSRImportantInformation', $record) && empty($record['GPSRImportantInformation']) && !empty($importantInformation)) {
            $record['GPSRImportantInformation'] = $importantInformation;
        }

        if (array_key_exists('GPSRWarningNoteFiles', $record) && empty($record['GPSRWarningNoteFiles']) && !empty($warningNoteFiles)) {
            $record['GPSRWarningNoteFiles'] = $warningNoteFiles;
        }

        if (array_key_exists('GPSRSafetyNoteFiles', $record) && empty($record['GPSRSafetyNoteFiles']) && !empty($safetyNoteFiles)) {
            $record['GPSRSafetyNoteFiles'] = $safetyNoteFiles;
        }

        if (array_key_exists('GPSRImportantInformationFiles', $record) && empty($record['GPSRImportantInformationFiles']) && !empty($importantInformationFiles)) {
            $record['GPSRImportantInformationFiles'] = $importantInformationFiles;
        }

        if (array_key_exists('GPSRReplaceDocuments', $record) && empty($record['GPSRReplaceDocuments'])) {
            $record['GPSRReplaceDocuments'] = $documentsReplacement;
        }

        return $record;
    }

    private function buildExportManufacturerRecord(array $record, array $manufacturer): array
    {
        $customFields = $manufacturer['customFields'] ?? [];
        $manufacturerName = array_key_exists('acris_gpsr_manufacturer_name', $customFields) && !empty($customFields['acris_gpsr_manufacturer_name']) ? $customFields['acris_gpsr_manufacturer_name']: '';
        $manufacturerStreet = array_key_exists('acris_gpsr_manufacturer_street', $customFields) && !empty($customFields['acris_gpsr_manufacturer_street']) ? $customFields['acris_gpsr_manufacturer_street']: '';
        $manufacturerHouseNumber = array_key_exists('acris_gpsr_manufacturer_house_number', $customFields) && !empty($customFields['acris_gpsr_manufacturer_house_number']) ? $customFields['acris_gpsr_manufacturer_house_number']: '';
        $manufacturerZipcode = array_key_exists('acris_gpsr_manufacturer_zipcode', $customFields) && !empty($customFields['acris_gpsr_manufacturer_zipcode']) ? $customFields['acris_gpsr_manufacturer_zipcode']: '';
        $manufacturerCity = array_key_exists('acris_gpsr_manufacturer_city', $customFields) && !empty($customFields['acris_gpsr_manufacturer_city']) ? $customFields['acris_gpsr_manufacturer_city']: '';
        $manufacturerCountry = array_key_exists('acris_gpsr_manufacturer_country', $customFields) && !empty($customFields['acris_gpsr_manufacturer_country']) ? $customFields['acris_gpsr_manufacturer_country']: '';
        $manufacturerPhoneNumber = array_key_exists('acris_gpsr_manufacturer_phone_number', $customFields) && !empty($customFields['acris_gpsr_manufacturer_phone_number']) ? $customFields['acris_gpsr_manufacturer_phone_number']: '';
        $manufacturerAddress = array_key_exists('acris_gpsr_manufacturer_address', $customFields) && !empty($customFields['acris_gpsr_manufacturer_address']) ? $customFields['acris_gpsr_manufacturer_address']: '';
        $manufacturerShopwareLink = array_key_exists('acris_gpsr_manufacturer_shopware_link', $customFields) && !empty($customFields['acris_gpsr_manufacturer_shopware_link']) ? $customFields['acris_gpsr_manufacturer_shopware_link']: '';
        $contactName = array_key_exists('acris_gpsr_contact_name', $customFields) && !empty($customFields['acris_gpsr_contact_name']) ? $customFields['acris_gpsr_contact_name']: '';
        $contactStreet = array_key_exists('acris_gpsr_contact_street', $customFields) && !empty($customFields['acris_gpsr_contact_street']) ? $customFields['acris_gpsr_contact_street']: '';
        $contactHouseNumber = array_key_exists('acris_gpsr_contact_house_number', $customFields) && !empty($customFields['acris_gpsr_contact_house_number']) ? $customFields['acris_gpsr_contact_house_number']: '';
        $contactZipcode = array_key_exists('acris_gpsr_contact_zipcode', $customFields) && !empty($customFields['acris_gpsr_contact_zipcode']) ? $customFields['acris_gpsr_contact_zipcode']: '';
        $contactCity = array_key_exists('acris_gpsr_contact_city', $customFields) && !empty($customFields['acris_gpsr_contact_city']) ? $customFields['acris_gpsr_contact_city']: '';
        $contactCountry = array_key_exists('acris_gpsr_contact_country', $customFields) && !empty($customFields['acris_gpsr_contact_country']) ? $customFields['acris_gpsr_contact_country']: '';
        $contactPhoneNumber = array_key_exists('acris_gpsr_contact_phone_number', $customFields) && !empty($customFields['acris_gpsr_contact_phone_number']) ? $customFields['acris_gpsr_contact_phone_number']: '';
        $contactAddress = array_key_exists('acris_gpsr_contact_address', $customFields) && !empty($customFields['acris_gpsr_contact_address']) ? $customFields['acris_gpsr_contact_address']: '';

        if (array_key_exists('GPSRManufacturerName', $record) && empty($record['GPSRManufacturerName']) && !empty($manufacturerName)) {
            $record['GPSRManufacturerName'] = $manufacturerName;
        }

        if (array_key_exists('GPSRManufacturerStreet', $record) && empty($record['GPSRManufacturerStreet']) && !empty($manufacturerStreet)) {
            $record['GPSRManufacturerStreet'] = $manufacturerStreet;
        }

        if (array_key_exists('GPSRManufacturerHouseNumber', $record) && empty($record['GPSRManufacturerHouseNumber']) && !empty($manufacturerHouseNumber)) {
            $record['GPSRManufacturerHouseNumber'] = $manufacturerHouseNumber;
        }

        if (array_key_exists('GPSRManufacturerZipcode', $record) && empty($record['GPSRManufacturerZipcode']) && !empty($manufacturerZipcode)) {
            $record['GPSRManufacturerZipcode'] = $manufacturerZipcode;
        }

        if (array_key_exists('GPSRManufacturerCity', $record) && empty($record['GPSRManufacturerCity']) && !empty($manufacturerCity)) {
            $record['GPSRManufacturerCity'] = $manufacturerCity;
        }

        if (array_key_exists('GPSRManufacturerCountry', $record) && empty($record['GPSRManufacturerCountry']) && !empty($manufacturerCountry)) {
            $record['GPSRManufacturerCountry'] = $manufacturerCountry;
        }

        if (array_key_exists('GPSRManufacturerPhoneNumber', $record) && empty($record['GPSRManufacturerPhoneNumber']) && !empty($manufacturerPhoneNumber)) {
            $record['GPSRManufacturerPhoneNumber'] = $manufacturerPhoneNumber;
        }

        if (array_key_exists('GPSRManufacturerAddress', $record) && empty($record['GPSRManufacturerAddress']) && !empty($manufacturerAddress)) {
            $record['GPSRManufacturerAddress'] = $manufacturerAddress;
        }

        if (array_key_exists('GPSRManufacturerShopwareLink', $record) && empty($record['GPSRManufacturerShopwareLink']) && !empty($manufacturerShopwareLink) && in_array($manufacturerShopwareLink, self::DEFAULT_MANUFACTURER_SHOPWARE_LINK_OPTIONS)) {
            $record['GPSRManufacturerShopwareLink'] = $manufacturerShopwareLink;
        }

        if (array_key_exists('GPSRContactName', $record) && empty($record['GPSRContactName']) && !empty($contactName)) {
            $record['GPSRContactName'] = $contactName;
        }

        if (array_key_exists('GPSRContactStreet', $record) && empty($record['GPSRContactStreet']) && !empty($contactStreet)) {
            $record['GPSRContactStreet'] = $contactStreet;
        }

        if (array_key_exists('GPSRContactHouseNumber', $record) && empty($record['GPSRContactHouseNumber']) && !empty($contactHouseNumber)) {
            $record['GPSRContactHouseNumber'] = $contactHouseNumber;
        }

        if (array_key_exists('GPSRContactZipcode', $record) && empty($record['GPSRContactZipcode']) && !empty($contactZipcode)) {
            $record['GPSRContactZipcode'] = $contactZipcode;
        }

        if (array_key_exists('GPSRContactCity', $record) && empty($record['GPSRContactCity']) && !empty($contactCity)) {
            $record['GPSRContactCity'] = $contactCity;
        }

        if (array_key_exists('GPSRContactCountry', $record) && empty($record['GPSRContactCountry']) && !empty($contactCountry)) {
            $record['GPSRContactCountry'] = $contactCountry;
        }

        if (array_key_exists('GPSRContactPhoneNumber', $record) && empty($record['GPSRContactPhoneNumber']) && !empty($contactPhoneNumber)) {
            $record['GPSRContactPhoneNumber'] = $contactPhoneNumber;
        }

        if (array_key_exists('GPSRContactAddress', $record) && empty($record['GPSRContactAddress']) && !empty($contactAddress)) {
            $record['GPSRContactAddress'] = $contactAddress;
        }

        return $record;
    }

    private function getDownloadFiles(?ProductGpsrDownloadCollection $productGpsrDownloadCollection): array
    {
        $warningNoteFiles = '';
        $safetyNoteFiles = '';
        $importantInformationFiles = '';

        if (empty($productGpsrDownloadCollection) || $productGpsrDownloadCollection->count() === 0) {
            return [$warningNoteFiles, $safetyNoteFiles, $importantInformationFiles];
        }

        /** @var ProductGpsrDownloadEntity $productGpsrDownload */
        foreach ($productGpsrDownloadCollection as $productGpsrDownload) {
            if ($productGpsrDownload->getGpsrType() === 'warning_note') {
                $file = $this->buildDownloadFile($productGpsrDownload);
                if (!empty($file)) {
                    if (empty($warningNoteFiles)) {
                        $warningNoteFiles = $file;
                    } else {
                        $warningNoteFiles .= '|' . $file;
                    }
                }
            } elseif ($productGpsrDownload->getGpsrType() === 'security_note') {
                $file = $this->buildDownloadFile($productGpsrDownload);
                if (!empty($file)) {
                    if (empty($safetyNoteFiles)) {
                        $safetyNoteFiles = $file;
                    } else {
                        $safetyNoteFiles .= '|' . $file;
                    }
                }
            } elseif ($productGpsrDownload->getGpsrType() === 'important_info') {
                $file = $this->buildDownloadFile($productGpsrDownload);
                if (!empty($file)) {
                    if (empty($importantInformationFiles)) {
                        $importantInformationFiles = $file;
                    } else {
                        $importantInformationFiles .= '|' . $file;
                    }
                }
            }
        }

        return [$warningNoteFiles, $safetyNoteFiles, $importantInformationFiles];
    }

    private function buildDownloadFile(ProductGpsrDownloadEntity $productGpsrDownload): ?string
    {
        $name = $productGpsrDownload->getMedia()->getFileName() . '.' . $productGpsrDownload->getMedia()->getFileExtension();
        if (!empty($productGpsrDownload->getTranslation('fileName'))) {
            $name .= '(' . $productGpsrDownload->getTranslation('fileName') . ')';
        }

        return $name;
    }

    private function skipProductRow(array $record): bool
    {
        return !array_key_exists('GPSRType', $record) && !array_key_exists('GPSRManufacturer', $record) && !array_key_exists('GPSRResponsiblePerson', $record) && !array_key_exists('GPSRWarningNote', $record)
            && !array_key_exists('GPSRSafetyNote', $record) && !array_key_exists('GPSRImportantInformation', $record) && !array_key_exists('GPSRWarningNoteFiles', $record)
            && !array_key_exists('GPSRSafetyNoteFiles', $record) && !array_key_exists('GPSRImportantInformationFiles', $record) && !array_key_exists('GPSRReplaceDocuments', $record);
    }

    private function skipManufacturerRow(array $record): bool
    {
        return !array_key_exists('GPSRManufacturerName', $record) && !array_key_exists('GPSRManufacturerStreet', $record) && !array_key_exists('GPSRManufacturerHouseNumber', $record) && !array_key_exists('GPSRManufacturerZipcode', $record)
            && !array_key_exists('GPSRManufacturerCity', $record) && !array_key_exists('GPSRManufacturerCountry', $record) && !array_key_exists('GPSRManufacturerPhoneNumber', $record)
            && !array_key_exists('GPSRManufacturerAddress', $record) && !array_key_exists('GPSRManufacturerShopwareLink', $record) && !array_key_exists('GPSRContactName', $record)
            && !array_key_exists('GPSRContactStreet', $record) && !array_key_exists('GPSRContactHouseNumber', $record) && !array_key_exists('GPSRContactZipcode', $record)
            && !array_key_exists('GPSRContactCity', $record) && !array_key_exists('GPSRContactCountry', $record) && !array_key_exists('GPSRContactPhoneNumber', $record) && !array_key_exists('GPSRContactAddress', $record);
    }

    private function buildImportProductRecord(array $record, array $data, Context $context): array
    {
        $productNumber = $data['productNumber'] ?? '';

        if (empty($productNumber)) {
            return $record;
        }

        $product = $this->productRepository->search((new Criteria())->addFilter(new EqualsFilter('productNumber', $productNumber)), $context)->first();

        if (empty($product) || !$product instanceof ProductEntity) {
            return $record;
        }

        $record['id'] = $product->getId();

        $customFields = $product->getCustomFields() ?? [];

        if (array_key_exists('GPSRType', $data) && !empty($data['GPSRType'])) {
            $customFields['acris_gpsr_product_type'] = $data['GPSRType'];
        }

        if (array_key_exists('GPSRManufacturer', $data) && !empty($data['GPSRManufacturer'])) {
            $customFields['acris_gpsr_product_manufacturer'] = $data['GPSRManufacturer'];
        }

        if (array_key_exists('GPSRResponsiblePerson', $data) && !empty($data['GPSRResponsiblePerson'])) {
            $customFields['acris_gpsr_product_contact'] = $data['GPSRResponsiblePerson'];
        }

        if (array_key_exists('GPSRWarningNote', $data) && !empty($data['GPSRWarningNote'])) {
            $customFields['acris_gpsr_product_hint_warning'] = $data['GPSRWarningNote'];
        }

        if (array_key_exists('GPSRSafetyNote', $data) && !empty($data['GPSRSafetyNote'])) {
            $customFields['acris_gpsr_product_hint_safety'] = $data['GPSRSafetyNote'];
        }

        if (array_key_exists('GPSRImportantInformation', $data) && !empty($data['GPSRImportantInformation'])) {
            $customFields['acris_gpsr_product_hint_information'] = $data['GPSRImportantInformation'];
        }

        $record['customFields'] = $customFields;

        $downloads = [];

        $replacement = array_key_exists('GPSRReplaceDocuments', $data) && boolval($data['GPSRReplaceDocuments']) === true;

        if (array_key_exists('GPSRWarningNoteFiles', $data) && !empty($data['GPSRWarningNoteFiles'])) {
            $downloads = $this->fillFiles($downloads, $data['GPSRWarningNoteFiles'], 'warning_note', $product, $context);
            if ($replacement === true) {
                $this->executeReplacement('acris_gpsr_p_d', 'warning_note', $product->getId());
            }
        }

        if (array_key_exists('GPSRSafetyNoteFiles', $data) && !empty($data['GPSRSafetyNoteFiles'])) {
            $downloads = $this->fillFiles($downloads, $data['GPSRSafetyNoteFiles'], 'security_note', $product, $context);
            if ($replacement === true) {
                $this->executeReplacement('acris_gpsr_p_d', 'security_note', $product->getId());
            }
        }

        if (array_key_exists('GPSRImportantInformationFiles', $data) && !empty($data['GPSRImportantInformationFiles'])) {
            $downloads = $this->fillFiles($downloads, $data['GPSRImportantInformationFiles'], 'important_info', $product, $context);
            if ($replacement === true) {
                $this->executeReplacement('acris_gpsr_p_d', 'important_info', $product->getId());
            }
        }

        $record['acrisGpsrDownloads'] = $downloads;

        return $record;
    }
    
    private function buildImportManufacturerRecord(array $record, array $data, Context $context): array
    {
        $manufacturerName = $data['ManufacturerName'] ?? '';

        if (empty($manufacturerName)) {
            return $record;
        }

        $manufacturer = $this->productManufacturerRepository->search((new Criteria())->addFilter(new EqualsFilter('name', $manufacturerName)), $context)->first();

        if (empty($manufacturer) || !$manufacturer instanceof ProductManufacturerEntity) {
            return $record;
        }

        $record['id'] = $manufacturer->getId();

        $customFields = $manufacturer->getCustomFields() ?? [];

        if (array_key_exists('GPSRManufacturerName', $data) && !empty($data['GPSRManufacturerName'])) {
            $customFields['acris_gpsr_manufacturer_name'] = $data['GPSRManufacturerName'];
        }

        if (array_key_exists('GPSRManufacturerStreet', $data) && !empty($data['GPSRManufacturerStreet'])) {
            $customFields['acris_gpsr_manufacturer_street'] = $data['GPSRManufacturerStreet'];
        }

        if (array_key_exists('GPSRManufacturerHouseNumber', $data) && !empty($data['GPSRManufacturerHouseNumber'])) {
            $customFields['acris_gpsr_manufacturer_house_number'] = $data['GPSRManufacturerHouseNumber'];
        }

        if (array_key_exists('GPSRManufacturerZipcode', $data) && !empty($data['GPSRManufacturerZipcode'])) {
            $customFields['acris_gpsr_manufacturer_zipcode'] = $data['GPSRManufacturerZipcode'];
        }

        if (array_key_exists('GPSRManufacturerCity', $data) && !empty($data['GPSRManufacturerCity'])) {
            $customFields['acris_gpsr_manufacturer_city'] = $data['GPSRManufacturerCity'];
        }

        if (array_key_exists('GPSRManufacturerCountry', $data) && !empty($data['GPSRManufacturerCountry'])) {
            $customFields['acris_gpsr_manufacturer_country'] = $data['GPSRManufacturerCountry'];
        }

        if (array_key_exists('GPSRManufacturerPhoneNumber', $data) && !empty($data['GPSRManufacturerPhoneNumber'])) {
            $customFields['acris_gpsr_manufacturer_phone_number'] = $data['GPSRManufacturerPhoneNumber'];
        }

        if (array_key_exists('GPSRManufacturerAddress', $data) && !empty($data['GPSRManufacturerAddress'])) {
            $customFields['acris_gpsr_manufacturer_address'] = $data['GPSRManufacturerAddress'];
        }

        if (array_key_exists('GPSRManufacturerShopwareLink', $data) && !empty($data['GPSRManufacturerShopwareLink'])) {
            $customFields['acris_gpsr_manufacturer_shopware_link'] = $data['GPSRManufacturerShopwareLink'];
        }

        if (array_key_exists('GPSRContactName', $data) && !empty($data['GPSRContactName'])) {
            $customFields['acris_gpsr_contact_name'] = $data['GPSRContactName'];
        }

        if (array_key_exists('GPSRContactStreet', $data) && !empty($data['GPSRContactStreet'])) {
            $customFields['acris_gpsr_contact_street'] = $data['GPSRContactStreet'];
        }

        if (array_key_exists('GPSRContactHouseNumber', $data) && !empty($data['GPSRContactHouseNumber'])) {
            $customFields['acris_gpsr_contact_house_number'] = $data['GPSRContactHouseNumber'];
        }

        if (array_key_exists('GPSRContactZipcode', $data) && !empty($data['GPSRContactZipcode'])) {
            $customFields['acris_gpsr_contact_zipcode'] = $data['GPSRContactZipcode'];
        }

        if (array_key_exists('GPSRContactCity', $data) && !empty($data['GPSRContactCity'])) {
            $customFields['acris_gpsr_contact_city'] = $data['GPSRContactCity'];
        }

        if (array_key_exists('GPSRContactCountry', $data) && !empty($data['GPSRContactCountry'])) {
            $customFields['acris_gpsr_contact_country'] = $data['GPSRContactCountry'];
        }

        if (array_key_exists('GPSRContactPhoneNumber', $data) && !empty($data['GPSRContactPhoneNumber'])) {
            $customFields['acris_gpsr_contact_phone_number'] = $data['GPSRContactPhoneNumber'];
        }

        if (array_key_exists('GPSRContactAddress', $data) && !empty($data['GPSRContactAddress'])) {
            $customFields['acris_gpsr_contact_address'] = $data['GPSRContactAddress'];
        }

        $record['customFields'] = $customFields;

        return $record;
    }

    private function fillFiles(array $downloads, string $fileData, string $type, ProductEntity $product, Context $context): array
    {
        $files = explode('|', $fileData);

        foreach ($files as $file) {
            $fileName = null;

            if (preg_match('/\((.*?)\)$/', $file, $matches)) {
                $fileName = $matches[1];
            }

            $download = [
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'fileName' => $fileName,
                    ],
                ],
                'gpsrType' => $type,
            ];

            $fileInfo = pathinfo(preg_replace('/\((.*?)\)$/', '', $file));

            $filename = $fileInfo['filename'];
            $extension = $fileInfo['extension'];

            $media = $this->mediaRepository->search((new Criteria())->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('fileName', $filename),
                new EqualsFilter('fileExtension', $extension),
            ])), $context)->first();

            if (empty($media) || !$media instanceof MediaEntity) {
                continue;
            }

            $download['id'] = md5($media->getId() . $product->getId() . $type);
            $download['mediaId'] = $media->getId();

            $id = $this->gpsrProductDownloadRepository->searchIds((new Criteria())->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('mediaId', $media->getId()),
                new EqualsFilter('productId', $product->getId()),
                new EqualsFilter('gpsrType', $type),
            ])), $context)->firstId();

            if (!empty($id) && $download['id'] !== $id) {
                $download['id'] = $id;
            }

            $downloads[] = $download;
        }

        return $downloads;
    }

    private function executeReplacement(string $table, string $key, string $productId): void
    {
        $sql = str_replace(
            ['#table#'],
            [$table],
            'DELETE FROM `#table#` WHERE `product_id` = :productId AND `gpsr_type` = :key;'
        );

        $this->connection->executeStatement($sql, ['productId' => Uuid::fromHexToBytes($productId), 'key' => $key]);
    }
}
