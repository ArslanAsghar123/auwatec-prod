<?php declare(strict_types=1);

namespace Acris\Gpsr;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use Shopware\Core\Content\ImportExport\ImportExportProfileEntity;
use Shopware\Core\Content\Media\Aggregate\MediaDefaultFolder\MediaDefaultFolderEntity;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Shopware\Core\System\Snippet\SnippetEntity;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class AcrisGpsrCS extends Plugin
{
    const DEFAULT_PRODUCT_GPSR_IMPORT_NAME = 'ACRIS GPSR: Product';
    const DEFAULT_PRODUCT_GPSR_IMPORT_NAME_EN = 'ACRIS GPSR: Product';
    const DEFAULT_PRODUCT_GPSR_IMPORT_NAME_DE = 'ACRIS GPSR: Produkt';
    const DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME = 'ACRIS GPSR: Manufacturer';
    const DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME_EN = 'ACRIS GPSR: Manufacturer';
    const DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME_DE = 'ACRIS GPSR: Hersteller';
    const CUSTOM_FIELD_SET_NAME_PRODUCT = 'acris_gpsr_product';
    const CUSTOM_FIELDS_PRODUCT = ['acris_gpsr_product_type', 'acris_gpsr_product_manufacturer', 'acris_gpsr_product_contact', 'acris_gpsr_product_hint_warning', 'acris_gpsr_product_hint_safety', 'acris_gpsr_product_hint_information'];
    const CUSTOM_FIELD_SET_NAME_MANUFACTURER = 'acris_gpsr_manufacturer';
    const CUSTOM_FIELDS_MANUFACTURER = ['acris_gpsr_manufacturer_name', 'acris_gpsr_manufacturer_street', 'acris_gpsr_manufacturer_house_number', 'acris_gpsr_manufacturer_zipcode', 'acris_gpsr_manufacturer_city', 'acris_gpsr_manufacturer_country', 'acris_gpsr_manufacturer_phone_number', 'acris_gpsr_manufacturer_address'];
    const CUSTOM_FIELD_SET_NAME_CONTACT = 'acris_gpsr_contact';
    const CUSTOM_FIELDS_CONTACT = ['acris_gpsr_contact_name', 'acris_gpsr_contact_street', 'acris_gpsr_contact_house_number', 'acris_gpsr_contact_zipcode', 'acris_gpsr_contact_city', 'acris_gpsr_contact_country', 'acris_gpsr_contact_phone_number', 'acris_gpsr_contact_address'];
    const CUSTOM_FIELD_SET_NAME_MANUFACTURER_ERP = 'acris_gpsr_manufacturer_erp';
    const CUSTOM_FIELDS_MANUFACTURER_ERP = ['acris_gpsr_manufacturer_name_erp', 'acris_gpsr_manufacturer_street_erp', 'acris_gpsr_manufacturer_house_number_erp', 'acris_gpsr_manufacturer_zipcode_erp', 'acris_gpsr_manufacturer_city_erp', 'acris_gpsr_manufacturer_country_erp', 'acris_gpsr_manufacturer_phone_number_erp_erp', 'acris_gpsr_manufacturer_address_erp'];
    const CUSTOM_FIELD_SET_NAME_CONTACT_ERP = 'acris_gpsr_contact_erp';
    const CUSTOM_FIELDS_CONTACT_ERP = ['acris_gpsr_contact_name_erp', 'acris_gpsr_contact_street_erp', 'acris_gpsr_contact_house_number_erp', 'acris_gpsr_contact_zipcode_erp', 'acris_gpsr_contact_city_erp', 'acris_gpsr_contact_country_erp', 'acris_gpsr_contact_phone_number_erp', 'acris_gpsr_contact_address_erp'];
    public const DEFAULT_IMAGE_MEDIA_FOLDER_NAME = "GPSR Images";
    public const DEFAULT_IMAGE_MEDIA_FOLDER_CUSTOM_FIELD = 'acrisGpsrImageDefaultFolder';
    public const DEFAULT_IMAGE_MEDIA_FOLDER_ENTITY_GPSR_NOTE = 'acris_gpsr_note';
    public const DEFAULT_IMAGE_MEDIA_FOLDER_ASSOCIATION_GPSR_NOTE = 'acrisGpsr';
    public const DEFAULT_MANUFACTURE_IMPORT_NAME_DE = "Hersteller";
    public const DEFAULT_MANUFACTURE_IMPORT_NAME_EN = "Manufacturer";

    public function install(InstallContext $installContext): void
    {
        $this->addCustomFields($installContext->getContext());
        $this->createDefaultMediaUploadFolder($installContext->getContext());
    }

    public function activate(ActivateContext $activateContext): void
    {
        $this->importDefaultManufacturerProfile($activateContext->getContext());
        $this->insertDefaultImportExportProfile($activateContext->getContext());
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        if(version_compare($updateContext->getCurrentPluginVersion(), '1.7.0', '<')
            && version_compare($updateContext->getUpdatePluginVersion(), '1.7.0', '>=')
            && $updateContext->getPlugin()->isActive()) {
            $this->importDefaultManufacturerProfile($updateContext->getContext());
        }

        if(version_compare($updateContext->getCurrentPluginVersion(), '2.9.0', '<')
            && version_compare($updateContext->getUpdatePluginVersion(), '2.9.0', '>=')
            && $updateContext->getPlugin()->isActive()) {
            $this->insertDefaultImportExportProfile($updateContext->getContext());
            $this->removeCustomFields($updateContext->getContext());
            $this->addCustomFields($updateContext->getContext());
        }

        if(version_compare($updateContext->getCurrentPluginVersion(), '2.11.0', '<')
            && version_compare($updateContext->getUpdatePluginVersion(), '2.11.0', '>=')
            && $updateContext->getPlugin()->isActive()) {
            $this->insertDefaultImportExportProfile($updateContext->getContext());
        }

        if(version_compare($updateContext->getCurrentPluginVersion(), '2.14.0', '<')
            && version_compare($updateContext->getUpdatePluginVersion(), '2.14.0', '>=')) {
            $this->removeCustomFields($updateContext->getContext());
            $this->addCustomFields($updateContext->getContext());
        }
    }

    private function addCustomFields(Context $context): void
    {
        /* Check for snippets if they exist for custom fields */
        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_PRODUCT);

        $customFieldSet = $this->container->get('custom_field_set.repository');
        if ($customFieldSet->search((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_PRODUCT)), $context)->count() > 0) return;

        $customFieldRepository = $this->container->get('custom_field.repository');
        if ($customFieldRepository->search((new Criteria())->addFilter(new EqualsAnyFilter('name', self::CUSTOM_FIELDS_PRODUCT)), $context)->count() > 0) return;

        $customFieldSet->create([[
            'name' => self::CUSTOM_FIELD_SET_NAME_PRODUCT,
            'config' => [
                'label' => [
                    'de-DE' => 'GPSR Produktsicherheitsverordnung Pro Product',
                    'en-GB' => 'GPSR Product Safety Regulation Pro Product'
                ]
            ],
            'relations' => [
                [
                    'entityName' => 'product'
                ]
            ],
            'customFields' => [
                ['name' => 'acris_gpsr_product_type', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Produktart und Produktidentifkatoren',
                            'en-GB' => 'Product type and product identifiers'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Produktart und Produktidentifkatoren eingeben...',
                            'en-GB' => 'Enter product type and product identifiers...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_product_manufacturer', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Herstellerinformationen',
                            'en-GB' => 'Manufacturer information'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Herstellerinformationen eingeben...',
                            'en-GB' => 'Enter manufacturer information...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_product_contact', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Verantwortliche Person (EU)',
                            'en-GB' => 'Responsible person (EU)'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Verantwortliche Person eingeben...',
                            'en-GB' => 'Enter responsible...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_product_hint_warning', 'type' => CustomFieldTypes::HTML,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'type' => 'html',
                        'customFieldType' => 'textEditor',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Warnhinweis',
                            'en-GB' => 'Warning note'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Warnhinweis eingeben...',
                            'en-GB' => 'Enter warning note...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_product_hint_safety', 'type' => CustomFieldTypes::HTML,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'type' => 'html',
                        'customFieldType' => 'textEditor',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Sicherheitshinweis',
                            'en-GB' => 'Safety note'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Sicherheitshinweis eingeben...',
                            'en-GB' => 'Enter safety note...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_product_hint_information', 'type' => CustomFieldTypes::HTML,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'type' => 'html',
                        'customFieldType' => 'textEditor',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Wichtige Information',
                            'en-GB' => 'Important information'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Wichtige Information eingeben...',
                            'en-GB' => 'Enter important information...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_product_replace_documents_mode', 'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'switch',
                        'customFieldType' => 'switch',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Dokumente ersetzen Modus',
                            'en-GB' => 'Documents replace mode'
                        ],
                    ]],
            ],
        ]], $context);

        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_MANUFACTURER);
        if ($customFieldSet->search((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_MANUFACTURER)), $context)->count() > 0) return;
        $customFieldSet->create([[
            'name' => self::CUSTOM_FIELD_SET_NAME_MANUFACTURER,
            'config' => [
                'label' => [
                    'de-DE' => 'GPSR Produktsicherheitsverordnung Pro Hersteller',
                    'en-GB' => 'GPSR Product Safety Regulation Pro Manufacturer'
                ]
            ],
            'relations' => [
                [
                    'entityName' => 'product_manufacturer'
                ]
            ],
            'customFields' => [
                ['name' => 'acris_gpsr_manufacturer_shopware_link', 'type' => CustomFieldTypes::SELECT,
                    'config' => [
                        'componentName' => 'sw-single-select',
                        'type' => CustomFieldTypes::SELECT,
                        'customFieldType' => CustomFieldTypes::SELECT,
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Link verhalten',
                            'en-GB' => 'Link behavior'
                        ],
                        'options' => [
                            [
                                'label' => [
                                    'en-GB' => 'Shopware Standard (link directly to the entered URL)',
                                    'de-DE' => 'Shopware Standard (Link direkt zur eingetragenen URL)'
                                ],
                                'value' => 'standard'
                            ],
                            [
                                'label' => [
                                    'en-GB' => 'Display as modal window',
                                    'de-DE' => 'Anzeige als Modalfenster'
                                ],
                                'value' => 'modal'
                            ]
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_name', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Herstellername',
                            'en-GB' => 'Manufacturer name'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Name eingeben...',
                            'en-GB' => 'Enter name...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_street', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Straße',
                            'en-GB' => 'Street'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Straße eingeben...',
                            'en-GB' => 'Enter street...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_house_number', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Hausnummer',
                            'en-GB' => 'House number'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Hausnummer eingeben...',
                            'en-GB' => 'Enter house number...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_zipcode', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'PLZ',
                            'en-GB' => 'Postcode'
                        ],
                        'placeholder' => [
                            'de-DE' => 'PLZ eingeben...',
                            'en-GB' => 'Enter postcode...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_city', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Ort',
                            'en-GB' => 'City'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Ort eingeben...',
                            'en-GB' => 'Enter city...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_country', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Land',
                            'en-GB' => 'Country'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Land eingeben...',
                            'en-GB' => 'Enter country...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_phone_number', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Telefonnummer',
                            'en-GB' => 'Phone number'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Telefonnummer eingeben...',
                            'en-GB' => 'Enter phone number...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_address', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Elektronische Adresse (E-Mail Adresse und/oder Website)',
                            'en-GB' => 'Electronic address (e-mail address and/or website)'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Elektronische Adresse eingeben...',
                            'en-GB' => 'Enter electronic address...'
                        ],
                    ]],
            ],
        ]], $context);

        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_MANUFACTURER_ERP);
        if ($customFieldSet->search((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_MANUFACTURER_ERP)), $context)->count() > 0) return;
        $customFieldSet->create([[
            'name' => self::CUSTOM_FIELD_SET_NAME_MANUFACTURER_ERP,
            'config' => [
                'label' => [
                    'de-DE' => '(Für ERP-Import) GPSR Produktsicherheitsverordnung Pro Hersteller',
                    'en-GB' => '(For ERP import) GPSR Product Safety Regulation Pro Manufacturer'
                ]
            ],
            'relations' => [
                [
                    'entityName' => 'product'
                ]
            ],
            'customFields' => [
                ['name' => 'acris_gpsr_manufacturer_shopware_link_erp', 'type' => CustomFieldTypes::SELECT,
                    'config' => [
                        'componentName' => 'sw-single-select',
                        'type' => CustomFieldTypes::SELECT,
                        'customFieldType' => CustomFieldTypes::SELECT,
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Link verhalten',
                            'en-GB' => 'Link behavior'
                        ],
                        'options' => [
                            [
                                'label' => [
                                    'en-GB' => 'Shopware Standard (link directly to the entered URL)',
                                    'de-DE' => 'Shopware Standard (Link direkt zur eingetragenen URL)'
                                ],
                                'value' => 'standard'
                            ],
                            [
                                'label' => [
                                    'en-GB' => 'Display as modal window',
                                    'de-DE' => 'Anzeige als Modalfenster'
                                ],
                                'value' => 'modal'
                            ]
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_name_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Herstellername',
                            'en-GB' => 'Manufacturer name'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Name eingeben...',
                            'en-GB' => 'Enter name...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_street_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Straße',
                            'en-GB' => 'Street'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Straße eingeben...',
                            'en-GB' => 'Enter street...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_house_number_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Hausnummer',
                            'en-GB' => 'House number'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Hausnummer eingeben...',
                            'en-GB' => 'Enter house number...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_zipcode_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'PLZ',
                            'en-GB' => 'Postcode'
                        ],
                        'placeholder' => [
                            'de-DE' => 'PLZ eingeben...',
                            'en-GB' => 'Enter postcode...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_city_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Ort',
                            'en-GB' => 'City'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Ort eingeben...',
                            'en-GB' => 'Enter city...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_country_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Land',
                            'en-GB' => 'Country'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Land eingeben...',
                            'en-GB' => 'Enter country...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_phone_number_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Telefonnummer',
                            'en-GB' => 'Phone number'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Telefonnummer eingeben...',
                            'en-GB' => 'Enter phone number...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_manufacturer_address_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Elektronische Adresse (E-Mail Adresse und/oder Website)',
                            'en-GB' => 'Electronic address (e-mail address and/or website)'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Elektronische Adresse eingeben...',
                            'en-GB' => 'Enter electronic address...'
                        ],
                    ]],
            ],
        ]], $context);

        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_CONTACT);
        if ($customFieldSet->search((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_CONTACT)), $context)->count() > 0) return;
        $customFieldSet->create([[
            'name' => self::CUSTOM_FIELD_SET_NAME_CONTACT,
            'config' => [
                'label' => [
                    'de-DE' => 'GPSR Produktsicherheitsverordnung Pro verantwortliche Person (EU)',
                    'en-GB' => 'GPSR Product Safety Regulation Pro responsible Person (EU)'
                ]
            ],
            'relations' => [
                [
                    'entityName' => 'product_manufacturer'
                ]
            ],
            'customFields' => [
                ['name' => 'acris_gpsr_contact_name', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Name',
                            'en-GB' => 'Name'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Name eingeben...',
                            'en-GB' => 'Enter name...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_street', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Straße',
                            'en-GB' => 'Street'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Straße eingeben...',
                            'en-GB' => 'Enter street...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_house_number', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Hausnummer',
                            'en-GB' => 'House number'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Hausnummer eingeben...',
                            'en-GB' => 'Enter house number...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_zipcode', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'PLZ',
                            'en-GB' => 'Postcode'
                        ],
                        'placeholder' => [
                            'de-DE' => 'PLZ eingeben...',
                            'en-GB' => 'Enter postcode...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_city', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Ort',
                            'en-GB' => 'City'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Ort eingeben...',
                            'en-GB' => 'Enter city...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_country', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Land',
                            'en-GB' => 'Country'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Land eingeben...',
                            'en-GB' => 'Enter country...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_phone_number', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Telefonnummer',
                            'en-GB' => 'Phone number'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Telefonnummer eingeben...',
                            'en-GB' => 'Enter phone number...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_address', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Elektronische Adresse (E-Mail Adresse und/oder Website)',
                            'en-GB' => 'Electronic address (e-mail address and/or website)'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Elektronische Adresse eingeben...',
                            'en-GB' => 'Enter electronic address...'
                        ],
                    ]],

            ],
        ]], $context);

        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_CONTACT_ERP);
        if ($customFieldSet->search((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_CONTACT_ERP)), $context)->count() > 0) return;
        $customFieldSet->create([[
            'name' => self::CUSTOM_FIELD_SET_NAME_CONTACT_ERP,
            'config' => [
                'label' => [
                    'de-DE' => '(Für ERP-Import) GPSR Produktsicherheitsverordnung Pro verantwortliche Person (EU)',
                    'en-GB' => '(For ERP import) GPSR Product Safety Regulation Pro responsible Person (EU)'
                ]
            ],
            'relations' => [
                [
                    'entityName' => 'product'
                ]
            ],
            'customFields' => [
                ['name' => 'acris_gpsr_contact_name_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Name',
                            'en-GB' => 'Name'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Name eingeben...',
                            'en-GB' => 'Enter name...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_street_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Straße',
                            'en-GB' => 'Street'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Straße eingeben...',
                            'en-GB' => 'Enter street...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_house_number_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Hausnummer',
                            'en-GB' => 'House number'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Hausnummer eingeben...',
                            'en-GB' => 'Enter house number...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_zipcode_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'PLZ',
                            'en-GB' => 'Postcode'
                        ],
                        'placeholder' => [
                            'de-DE' => 'PLZ eingeben...',
                            'en-GB' => 'Enter postcode...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_city_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Ort',
                            'en-GB' => 'City'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Ort eingeben...',
                            'en-GB' => 'Enter city...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_country_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Land',
                            'en-GB' => 'Country'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Land eingeben...',
                            'en-GB' => 'Enter country...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_phone_number_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Telefonnummer',
                            'en-GB' => 'Phone number'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Telefonnummer eingeben...',
                            'en-GB' => 'Enter phone number...'
                        ],
                    ]],
                ['name' => 'acris_gpsr_contact_address_erp', 'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-field',
                        'type' => 'text',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1,
                        'label' => [
                            'de-DE' => 'Elektronische Adresse (E-Mail Adresse und/oder Website)',
                            'en-GB' => 'Electronic address (e-mail address and/or website)'
                        ],
                        'placeholder' => [
                            'de-DE' => 'Elektronische Adresse eingeben...',
                            'en-GB' => 'Enter electronic address...'
                        ],
                    ]],

            ],
        ]], $context);
    }

    private function createDefaultMediaUploadFolder(Context $context): void
    {
        $imageMediaUploadFolderId = Uuid::randomHex();
        $imageDefaultMediaUploadFolderId = Uuid::randomHex();

        $defaultMediaFolderRepository = $this->container->get('media_default_folder.repository');
        $mediaFolderRepository = $this->container->get('media_folder.repository');
        $imageDefaultExistingMediaUploadFolder = $this->getImageDefaultMediaUploadFolder($defaultMediaFolderRepository, $context);
        $imageExistingMediaUploadFolder = $this->getImageMediaUploadFolder($mediaFolderRepository, $context);

        if (!$imageDefaultExistingMediaUploadFolder instanceof MediaDefaultFolderEntity) {
            $defaultMediaUploadFolder = [
                'id' => $imageDefaultMediaUploadFolderId,
                'entity' => self::DEFAULT_IMAGE_MEDIA_FOLDER_ENTITY_GPSR_NOTE,
                'associationFields' => [self::DEFAULT_IMAGE_MEDIA_FOLDER_ASSOCIATION_GPSR_NOTE],
                'customFields' => [
                    self::DEFAULT_IMAGE_MEDIA_FOLDER_CUSTOM_FIELD => true
                ]
            ];
            $this->container->get('media_default_folder.repository')->create([$defaultMediaUploadFolder], $context);
        } else {
            $imageDefaultMediaUploadFolderId = $imageDefaultExistingMediaUploadFolder->getId();
        }

        if (!$imageExistingMediaUploadFolder instanceof MediaFolderEntity) {
            $mediaThumbnailSizes = $this->container->get('media_thumbnail_size.repository')->search((new Criteria()), $context);
            $thumbnails = [];
            if (!empty($mediaThumbnailSizes) && $mediaThumbnailSizes instanceof EntitySearchResult && $mediaThumbnailSizes->count() > 0) {
                foreach ($mediaThumbnailSizes->getElements() as $element) {
                    $thumbnails[] = ['id' => $element->getId()];
                }
            }

            $defaultMediaUploadFolder = [
                'id' => $imageMediaUploadFolderId,
                'name' => self::DEFAULT_IMAGE_MEDIA_FOLDER_NAME,
                'useParentConfiguration' => false,
                'defaultFolderId' => $imageDefaultMediaUploadFolderId,
                'configuration' => [
                    'createThumbnails' => true,
                    'keepAspectRatio' => true,
                    'thumbnailQuality' => 80,
                    'private' => false,
                    'noAssociation' => false,
                    'mediaThumbnailSizes' => $thumbnails
                ],
                'customFields' => [
                    self::DEFAULT_IMAGE_MEDIA_FOLDER_CUSTOM_FIELD => true
                ]
            ];
            $this->container->get('media_folder.repository')->create([$defaultMediaUploadFolder], $context);
        }
    }

    private function getImageDefaultMediaUploadFolder(EntityRepository $mediaFolderRepository, Context $context): ?MediaDefaultFolderEntity
    {
        return $mediaFolderRepository->search(
            (new Criteria())
                ->addAssociation('folder')
                ->addAssociation('folder.media')
                ->addAssociation('folder.configuration')
                ->addAssociation('folder.configuration.mediaFolders')
                ->addFilter(new EqualsFilter('customFields.' . self::DEFAULT_IMAGE_MEDIA_FOLDER_CUSTOM_FIELD, 'true')),
            $context)->first();
    }

    private function getImageMediaUploadFolder(EntityRepository $mediaFolderRepository, Context $context): ?MediaFolderEntity
    {
        return $mediaFolderRepository->search(
            (new Criteria())
                ->addAssociation('media')
                ->addAssociation('configuration')
                ->addAssociation('configuration.mediaFolders')
                ->addFilter(new EqualsFilter('customFields.' . self::DEFAULT_IMAGE_MEDIA_FOLDER_CUSTOM_FIELD, 'true')),
            $context)->first();
    }

    public function uninstall(UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        $this->removeImportExportProfiles($context->getContext());
        $this->removeCustomFields($context->getContext());
        $this->removeMediaUploadFolder($context->getContext());
        $this->cleanupDatabase();
    }

    private function removeCustomFields(Context $context): void
    {
        $customFieldSet = $this->container->get('custom_field_set.repository');

        /* Check for snippets if they exist for custom fields */
        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_PRODUCT);
        $id = $customFieldSet->searchIds((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_PRODUCT)), $context)->firstId();
        if ($id) $customFieldSet->delete([['id' => $id]], $context);
        /* Check for snippets if they exist for custom fields */
        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_MANUFACTURER);
        $id = $customFieldSet->searchIds((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_MANUFACTURER)), $context)->firstId();
        if ($id) $customFieldSet->delete([['id' => $id]], $context);
        /* Check for snippets if they exist for custom fields */
        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_CONTACT);
        $id = $customFieldSet->searchIds((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_CONTACT)), $context)->firstId();
        if ($id) $customFieldSet->delete([['id' => $id]], $context);
        /* Check for snippets if they exist for custom fields */
        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_MANUFACTURER_ERP);
        $id = $customFieldSet->searchIds((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_MANUFACTURER_ERP)), $context)->firstId();
        if ($id) $customFieldSet->delete([['id' => $id]], $context);
        /* Check for snippets if they exist for custom fields */
        $this->checkForExistingCustomFieldSnippets($context, self::CUSTOM_FIELDS_CONTACT_ERP);
        $id = $customFieldSet->searchIds((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_CONTACT_ERP)), $context)->firstId();
        if ($id) $customFieldSet->delete([['id' => $id]], $context);
    }

    private function removeMediaUploadFolder(Context $context): void
    {
        $defaultMediaFolderRepository = $this->container->get('media_default_folder.repository');
        $mediaFolderRepository = $this->container->get('media_folder.repository');
        $imageMediaUploadFolder = $this->getImageMediaUploadFolder($mediaFolderRepository, $context);
        $imageDefaultMediaUploadFolder = $this->getImageDefaultMediaUploadFolder($defaultMediaFolderRepository, $context);

        $this->removeImageDefaultFolderByEntityName($this->container->get(Connection::class), 'acris_gpsr_p_d', 'acris_gprs_product_download');

        if ($imageDefaultMediaUploadFolder instanceof MediaDefaultFolderEntity) {
            $defaultMediaFolderRepository->delete([['id' => $imageDefaultMediaUploadFolder->getId()]], $context);
        }

        if (!$imageMediaUploadFolder instanceof MediaFolderEntity) {
            return;
        }

        if ($imageMediaUploadFolder->getMedia() && $imageMediaUploadFolder->getMedia()->count() > 0) {
            return;
        }

        $defaultMediaUploadFolderConfiguration = $imageMediaUploadFolder->getConfiguration();
        $deleteConfigurationId = null;
        if ($defaultMediaUploadFolderConfiguration && $defaultMediaUploadFolderConfiguration->getMediaFolders()) {
            if ($defaultMediaUploadFolderConfiguration->getMediaFolders()->count() < 2) {
                $deleteConfigurationId = $defaultMediaUploadFolderConfiguration->getId();
            }
        }

        if ($deleteConfigurationId !== null) {
            $this->container->get('media_folder_configuration.repository')->delete([['id' => $deleteConfigurationId]], $context);
        }

        $mediaFolderRepository->delete([['id' => $imageMediaUploadFolder->getId()]], $context);
    }

    private function removeImageDefaultFolderByEntityName(Connection $connection, string $newEntityName, string $oldEntityName = ''): void
    {
        $connection->executeStatement(
            'DELETE FROM `media_default_folder` WHERE `entity` = :newEntityName' . ($oldEntityName ? ' OR `entity` = :oldEntityName' : ''),
            array_filter(['newEntityName' => $newEntityName, 'oldEntityName' => $oldEntityName])
        );
    }

    private function cleanupDatabase(): void
    {
        $connection = $this->container->get(Connection::class);

        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_p_d_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_p_d');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gprs_product_download');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_c_d_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_c_d');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_contact_download');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_n_d_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_n_d');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_note_download');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_mf_d_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_mf_d');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_manufacturer_download');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_mf_d_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_mf_d');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gprs_product_download');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_contact_download');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_note_download');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_manufacturer_download');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_manufacturer_download');

        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_mf_stream');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_contact_stream');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_note_stream');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_mf_sales_channel');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_contact_sales_channel');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_note_sales_channel');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_mf_rule');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_contact_rule');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_note_rule');

        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_mf_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_contact_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_note_translation');

        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_mf');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_contact');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_gpsr_note');


        if ($this->columnExists($connection, 'product', 'acrisGpsrDownloads')) {
            $this->removeInheritance($connection, 'product', 'acrisGpsrDownloads');
        }
        if ($this->columnExists($connection, 'media', 'acrisGpsrDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrDownloads');
        }

        if ($this->columnExists($connection, 'product', 'acrisGpsrContactDownloads')) {
            $this->removeInheritance($connection, 'product', 'acrisGpsrContactDownloads');
        }
        if ($this->columnExists($connection, 'media', 'acrisGpsrContactDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrContactDownloads');
        }

        if ($this->columnExists($connection, 'product', 'acrisGpsrNoteDownloads')) {
            $this->removeInheritance($connection, 'product', 'acrisGpsrNoteDownloads');
        }
        if ($this->columnExists($connection, 'media', 'acrisGpsrNoteDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrNoteDownloads');
        }

        if ($this->columnExists($connection, 'product', 'acrisGpsrManufacturerDownloads')) {
            $this->removeInheritance($connection, 'product', 'acrisGpsrManufacturerDownloads');
        }
        if ($this->columnExists($connection, 'media', 'acrisGpsrManufacturerDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrManufacturerDownloads');
        }

        if ($this->columnExists($connection, 'product', 'acrisManufacturerDownloads')) {
            $this->removeInheritance($connection, 'product', 'acrisManufacturerDownloads');
        }
        if ($this->columnExists($connection, 'media', 'acrisManufacturerDownloads')) {
            $this->removeInheritance($connection, 'media', 'acrisManufacturerDownloads');
        }

        if ($this->columnExists($connection, 'rule', 'acrisGpsrManufacturers')) {
            $this->removeInheritance($connection, 'rule', 'acrisGpsrManufacturers');
        }
        if ($this->columnExists($connection, 'sales_channel', 'acrisGpsrManufacturers')) {
            $this->removeInheritance($connection, 'sales_channel', 'acrisGpsrManufacturers');
        }
        if ($this->columnExists($connection, 'product_stream', 'acrisGpsrManufacturers')) {
            $this->removeInheritance($connection, 'product_stream', 'acrisGpsrManufacturers');
        }
        if ($this->columnExists($connection, 'rule', 'acrisGpsrContacts')) {
            $this->removeInheritance($connection, 'rule', 'acrisGpsrContacts');
        }
        if ($this->columnExists($connection, 'sales_channel', 'acrisGpsrContacts')) {
            $this->removeInheritance($connection, 'sales_channel', 'acrisGpsrContacts');
        }
        if ($this->columnExists($connection, 'product_stream', 'acrisGpsrContacts')) {
            $this->removeInheritance($connection, 'product_stream', 'acrisGpsrContacts');
        }
        if ($this->columnExists($connection, 'media', 'acrisGpsrNotes')) {
            $this->removeInheritance($connection, 'media', 'acrisGpsrNotes');
        }
        if ($this->columnExists($connection, 'rule', 'acrisGpsrNotes')) {
            $this->removeInheritance($connection, 'rule', 'acrisGpsrNotes');
        }
        if ($this->columnExists($connection, 'sales_channel', 'acrisGpsrNotes')) {
            $this->removeInheritance($connection, 'sales_channel', 'acrisGpsrNotes');
        }
        if ($this->columnExists($connection, 'product_stream', 'acrisGpsrNotes')) {
            $this->removeInheritance($connection, 'product_stream', 'acrisGpsrNotes');
        }
    }

    protected function columnExists(Connection $connection, string $table, string $column): bool
    {
        $exists = $connection->fetchOne(
            'SHOW COLUMNS FROM `' . $table . '` WHERE `Field` LIKE :column',
            ['column' => $column]
        );
        return !empty($exists);
    }

    private function removeInheritance(Connection $connection, string $entity, string $propertyName): void
    {
        $sql = str_replace(
            ['#table#', '#column#'],
            [$entity, $propertyName],
            'ALTER TABLE `#table#` DROP `#column#`'
        );

        $connection->executeStatement($sql);
    }

    private function checkForExistingCustomFieldSnippets(Context $context, array $customFieldSnippets): void
    {
        /** @var EntityRepository $snippetRepository */
        $snippetRepository = $this->container->get('snippet.repository');

        $criteria = new Criteria();
        $filter = [];
        foreach ($customFieldSnippets as $snippet) {
            $filter[] = new EqualsFilter('translationKey', 'customFields.' . $snippet);
        }
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $filter));

        $searchResult = $snippetRepository->search($criteria, $context);

        if ($searchResult->count() > 0) {
            $snippetIds = [];
            /** @var SnippetEntity $snippet */
            foreach ($searchResult->getEntities()->getElements() as $snippet) {
                $snippetIds[] = [
                    'id' => $snippet->getId()
                ];
            }

            if (!empty($snippetIds)) {
                $snippetRepository->delete($snippetIds, $context);
            }
        }
    }

    private function importDefaultManufacturerProfile(Context $context): void
    {
        $profileRepository = $this->container->get('import_export_profile.repository');
        $defaultManufacturerProfile =
            [
                'name' => self::DEFAULT_MANUFACTURE_IMPORT_NAME_EN,
                'fileType' => 'text/csv',
                'delimiter' => ';',
                'enclosure' => '"',
                'sourceEntity' => 'product_manufacturer',
                'mapping' => $this->getDefaultImportProfileMappingData(),
                'systemDefault' => true,
                'translations' => [
                    'de-DE' => [
                        'label' => self::DEFAULT_MANUFACTURE_IMPORT_NAME_DE
                    ],
                    'en-GB' => [
                        'label' => self::DEFAULT_MANUFACTURE_IMPORT_NAME_EN
                    ],
                    [
                        'label' => self::DEFAULT_MANUFACTURE_IMPORT_NAME_EN,
                        'languageId' => Defaults::LANGUAGE_SYSTEM
                    ]
                ]
            ];

        $this->createImportProfileIfNotExists($profileRepository, $context, $defaultManufacturerProfile);
    }

    private function insertDefaultImportExportProfile(Context $context): void
    {
        /** @var EntityRepository $importExportProfileRepository */
        $importExportProfileRepository = $this->container->get('import_export_profile.repository');

        $defaultImportExportProfiles = [
            [
                'name' => self::DEFAULT_PRODUCT_GPSR_IMPORT_NAME,
                'label' => self::DEFAULT_PRODUCT_GPSR_IMPORT_NAME,
                'systemDefault' => true,
                'sourceEntity' => 'product',
                'fileType' => 'text/csv',
                'delimiter' => ';',
                'enclosure' => '"',
                'technicalName' => 'acris_gpsr_product',
                'config' => [
                    'createEntities' => false,
                    'updateEntities' => true
                ],
                'translations' => [
                    'de-DE' => [
                        'label' => self::DEFAULT_PRODUCT_GPSR_IMPORT_NAME_DE
                    ],
                    'en-GB' => [
                        'label' => self::DEFAULT_PRODUCT_GPSR_IMPORT_NAME_EN
                    ],
                    [
                        'label' => self::DEFAULT_PRODUCT_GPSR_IMPORT_NAME_EN,
                        'languageId' => Defaults::LANGUAGE_SYSTEM
                    ]
                ],
                'mapping' => [
                    [
                        "key" => "productNumber",
                        "mappedKey" => "productNumber",
                        "position" => 0
                    ],
                    [
                        "key" => "customFields.acris_gpsr_product_type",
                        "mappedKey" => "GPSRType",
                        "position" => 1
                    ],
                    [
                        "key" => "customFields.acris_gpsr_product_manufacturer",
                        "mappedKey" => "GPSRManufacturer",
                        "position" => 2
                    ],
                    [
                        "key" => "customFields.acris_gpsr_product_contact",
                        "mappedKey" => "GPSRResponsiblePerson",
                        "position" => 3
                    ],
                    [
                        "key" => "customFields.acris_gpsr_product_hint_warning",
                        "mappedKey" => "GPSRWarningNote",
                        "position" => 4
                    ],
                    [
                        "key" => "customFields.acris_gpsr_product_hint_safety",
                        "mappedKey" => "GPSRSafetyNote",
                        "position" => 5
                    ],
                    [
                        "key" => "customFields.acris_gpsr_product_hint_information",
                        "mappedKey" => "GPSRImportantInformation",
                        "position" => 6
                    ],
                    [
                        "key" => "acrisGpsrDownloads.media.fileExtension",
                        "mappedKey" => "GPSRWarningNoteFiles",
                        "position" => 7
                    ],
                    [
                        "key" => "acrisGpsrDownloads.fileName",
                        "mappedKey" => "GPSRSafetyNoteFiles",
                        "position" => 8
                    ],
                    [
                        "key" => "acrisGpsrDownloads.media.fileName",
                        "mappedKey" => "GPSRImportantInformationFiles",
                        "position" => 9
                    ],
                    [
                        "key" => "customFields.acris_gpsr_product_replace_documents_mode",
                        "mappedKey" => "GPSRReplaceDocuments",
                        "position" => 10
                    ]
                ]
            ],
            [
                'name' => self::DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME,
                'label' => self::DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME,
                'systemDefault' => true,
                'sourceEntity' => 'product_manufacturer',
                'fileType' => 'text/csv',
                'delimiter' => ';',
                'enclosure' => '"',
                'technicalName' => 'acris_gpsr_manufacturer',
                'config' => [
                    'createEntities' => false,
                    'updateEntities' => true
                ],
                'translations' => [
                    'de-DE' => [
                        'label' => self::DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME_DE
                    ],
                    'en-GB' => [
                        'label' => self::DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME_EN
                    ],
                    [
                        'label' => self::DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME_EN,
                        'languageId' => Defaults::LANGUAGE_SYSTEM
                    ]
                ],
                'mapping' => [
                    [
                        "key" => "translations.DEFAULT.name",
                        "mappedKey" => "ManufacturerName",
                        "position" => 0
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_name",
                        "mappedKey" => "GPSRManufacturerName",
                        "position" => 1
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_street",
                        "mappedKey" => "GPSRManufacturerStreet",
                        "position" => 2
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_house_number",
                        "mappedKey" => "GPSRManufacturerHouseNumber",
                        "position" => 3
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_zipcode",
                        "mappedKey" => "GPSRManufacturerZipcode",
                        "position" => 4
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_city",
                        "mappedKey" => "GPSRManufacturerCity",
                        "position" => 5
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_country",
                        "mappedKey" => "GPSRManufacturerCountry",
                        "position" => 6
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_phone_number",
                        "mappedKey" => "GPSRManufacturerPhoneNumber",
                        "position" => 7
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_address",
                        "mappedKey" => "GPSRManufacturerAddress",
                        "position" => 8
                    ],
                    [
                        "key" => "customFields.acris_gpsr_manufacturer_shopware_link",
                        "mappedKey" => "GPSRManufacturerShopwareLink",
                        "position" => 9
                    ],
                    [
                        "key" => "customFields.acris_gpsr_contact_name",
                        "mappedKey" => "GPSRContactName",
                        "position" => 10
                    ],
                    [
                        "key" => "customFields.acris_gpsr_contact_street",
                        "mappedKey" => "GPSRContactStreet",
                        "position" => 11
                    ],
                    [
                        "key" => "customFields.acris_gpsr_contact_house_number",
                        "mappedKey" => "GPSRContactHouseNumber",
                        "position" => 12
                    ],
                    [
                        "key" => "customFields.acris_gpsr_contact_zipcode",
                        "mappedKey" => "GPSRContactZipcode",
                        "position" => 13
                    ],
                    [
                        "key" => "customFields.acris_gpsr_contact_city",
                        "mappedKey" => "GPSRContactCity",
                        "position" => 14
                    ],
                    [
                        "key" => "customFields.acris_gpsr_contact_country",
                        "mappedKey" => "GPSRContactCountry",
                        "position" => 15
                    ],
                    [
                        "key" => "customFields.acris_gpsr_contact_phone_number",
                        "mappedKey" => "GPSRContactPhoneNumber",
                        "position" => 16
                    ],
                    [
                        "key" => "customFields.acris_gpsr_contact_address",
                        "mappedKey" => "GPSRContactAddress",
                        "position" => 17
                    ]
                ]
            ]
        ];

        foreach ($defaultImportExportProfiles as $defaultImportExportProfile) {
            $this->createIfNotExists($importExportProfileRepository, [['name' => 'name', 'value' => $defaultImportExportProfile['name']]], $defaultImportExportProfile, $context);
        }
    }

    private function createIfNotExists(EntityRepository $repository, array $equalFields, array $data, Context $context): void
    {
        $filters = [];
        foreach ($equalFields as $equalField) {
            $filters[] = new EqualsFilter($equalField['name'], $equalField['value']);
        }
        if (sizeof($filters) > 1) {
            $filter = new MultiFilter(MultiFilter::CONNECTION_OR, $filters);
        } else {
            $filter = array_shift($filters);
        }

        $searchResult = $repository->search((new Criteria())->addFilter($filter), $context);
        if ($searchResult->count() == 0) {
            $repository->create([$data], $context);
        }
    }

    private function getDefaultImportProfileMappingData() : array
    {
        return array(
            array("key" => "id", "mappedKey" => "id", "position" => 0),
            array("key" => "translations.DEFAULT.name", "mappedKey" => "name", "position" => 1)
        );
    }

    private function createImportProfileIfNotExists(
        EntityRepository $entityRepository,
        Context $context,
        array $profileData
    ): void
    {
        $exists = $entityRepository->search((new Criteria())
            ->addFilter(new EqualsFilter('name', $profileData['name'])), $context);
        if($exists->getTotal() === 0) {
            $entityRepository->create([$profileData], $context);
        }
    }

    private function removeImportExportProfiles(Context $context): void
    {
        $connection = $this->container->get(Connection::class);

        $importExportProfileRepository = $this->container->get('import_export_profile.repository');
        $importExportLogRepository = $this->container->get('import_export_log.repository');

        /** @var EntitySearchResult $searchResult */
        $searchResult = $importExportProfileRepository->search((new Criteria())->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('sourceEntity', 'product_manufacturer'),
                new MultiFilter(MultiFilter::CONNECTION_OR, [
                    new EqualsFilter('name', self::DEFAULT_MANUFACTURE_IMPORT_NAME_EN)
                ])
            ]),
            new EqualsAnyFilter('name', [self::DEFAULT_PRODUCT_GPSR_IMPORT_NAME, self::DEFAULT_MANUFACTURER_GPSR_IMPORT_NAME])
        ])), $context);

        $ids = [];
        if($searchResult->getTotal() > 0 && $searchResult->first()) {

            /** @var ImportExportProfileEntity $entity */
            foreach ($searchResult->getEntities()->getElements() as $entity) {

                if ($entity->getSystemDefault() === true) {
                    $importExportProfileRepository->update([
                        ['id' => $entity->getId(), 'systemDefault' => false ]
                    ], $context);
                }

                /** @var EntitySearchResult $logResult */
                $logResult = $importExportLogRepository->search((new Criteria())->addFilter(new EqualsFilter('profileId', $entity->getId())), $context);
                if ($logResult->getTotal() > 0 && $logResult->first()) {
                    /** @var ImportExportLogEntity $logEntity */
                    foreach ($logResult->getEntities() as $logEntity) {
                        $stmt = $connection->prepare("UPDATE import_export_log SET profile_id = :profileId WHERE id = :id");
                        $stmt->execute(['profileId' => null, 'id' => Uuid::fromHexToBytes($logEntity->getId()) ]);
                    }
                }

                $ids[] = ['id' => $entity->getId()];
            }
            $importExportProfileRepository->delete($ids, $context);
        }
    }
}
