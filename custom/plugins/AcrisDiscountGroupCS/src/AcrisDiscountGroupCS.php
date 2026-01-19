<?php declare(strict_types=1);

namespace Acris\DiscountGroup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\ImportExport\ImportExportProfileEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Shopware\Core\System\Snippet\SnippetEntity;

class AcrisDiscountGroupCS extends Plugin
{
    /** @deprecated  */
    const CUSTOM_FIELD_SET_NAME_DISCOUNT_GROUP = 'acris_discount_group';
    const CUSTOM_FIELD_SET_NAME_CUSTOMER_DISCOUNT_GROUP = 'acris_discount_group_customer';
    const CUSTOM_FIELD_SET_NAME_PRODUCT_DISCOUNT_GROUP = 'acris_discount_group_product';
    const IMPORT_EXPORT_PROFILE_NAME = 'ACRIS Discount Groups';
    const DEFAULT_MAIL_TEMPLATE_TYPE_DISCOUNT_GROUP = 'acris_discount_group.discount_group';

    public function getTemplatePriority(): int
    {
        return 100;
    }

    public function install(InstallContext $context): void
    {
        $this->addCustomFields($context->getContext());
        $this->addImportExportProfile($context->getContext());
    }

    public function activate(Plugin\Context\ActivateContext $context): void
    {
        $this->insertDefaultMailTemplate($context->getContext());
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        if(version_compare($updateContext->getCurrentPluginVersion(), '1.2.0', '<') && $updateContext->getPlugin()->isActive() === true) {
            $this->addImportExportProfile($updateContext->getContext());
        }

        if(version_compare($updateContext->getCurrentPluginVersion(), '1.4.0', '<')
            && version_compare($updateContext->getUpdatePluginVersion(), '1.4.0', '>=')) {
            $this->addCustomFields($updateContext->getContext());
        }

        if(version_compare($updateContext->getCurrentPluginVersion(), '5.2.0', '<')
            && version_compare($updateContext->getUpdatePluginVersion(), '5.2.0', '>=')) {
            $this->insertDefaultMailTemplate($updateContext->getContext());
        }

        if(version_compare($updateContext->getCurrentPluginVersion(), '6.0.9', '<')
            && version_compare($updateContext->getUpdatePluginVersion(), '6.0.9', '>=')) {
            $this->updateDefaultImportExportProfile($updateContext->getContext());
        }
    }

    public function uninstall(UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }
        $this->cleanupImportExportProfile($context->getContext());
        $this->removeCustomFields($context->getContext(), [self::CUSTOM_FIELD_SET_NAME_DISCOUNT_GROUP, self::CUSTOM_FIELD_SET_NAME_PRODUCT_DISCOUNT_GROUP, self::CUSTOM_FIELD_SET_NAME_CUSTOMER_DISCOUNT_GROUP]);
        $this->cleanupDatabase();
        $this->removeDefaultMailTemplate($context->getContext());
    }

    private function cleanupDatabase(): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('DROP TABLE IF EXISTS acris_discount_group_rule');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_discount_dynamic_groups');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_discount_group_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_discount_group');

        if ($this->columnExists($connection, 'rule', 'acrisDiscountGroups')) {
            $connection->executeStatement('ALTER TABLE `rule` DROP COLUMN `acrisDiscountGroups`');
        }
        if ($this->columnExists($connection, 'product_stream', 'acrisDiscountGroups')) {
            $connection->executeStatement('ALTER TABLE `product_stream` DROP COLUMN `acrisDiscountGroups`');
        }
        if ($this->columnExists($connection, 'product', 'acrisDiscountGroups')) {
            $connection->executeStatement('ALTER TABLE `product` DROP COLUMN `acrisDiscountGroups`');
        }
        if ($this->columnExists($connection, 'customer', 'acrisDiscountGroups')) {
            $connection->executeStatement('ALTER TABLE `customer` DROP COLUMN `acrisDiscountGroups`');
        }
    }

    private function addCustomFields(Context $context): void
    {
        /* Check for snippets if they exist for custom fields */
        $this->checkForExistingCustomFieldSnippets($context);
        $this->removeCustomFields($context, [self::CUSTOM_FIELD_SET_NAME_DISCOUNT_GROUP]);

        $customFieldSet = $this->container->get('custom_field_set.repository');
        if($customFieldSet->search((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_CUSTOMER_DISCOUNT_GROUP)), $context)->count() == 0) {
            $customFieldSet->create([[
                'name' => self::CUSTOM_FIELD_SET_NAME_CUSTOMER_DISCOUNT_GROUP,
                'config' => [
                    'label' => [
                        'en-GB' => 'Customer discount group',
                        'de-DE' => 'Kunden-Rabattgruppe'
                    ]
                ],
                'customFields' => [
                    ['name' => 'acris_discount_group_customer_value', 'type' => CustomFieldTypes::TEXT,
                        'config' => [
                            'componentName' => 'sw-field',
                            'type' => 'text',
                            'customFieldType' => 'text',
                            'customFieldPosition' => 1,
                            'label' => [
                                'en-GB' => 'Customer discount group',
                                'de-DE' => 'Kunden-Rabattgruppe'
                            ],
                            'helpText' => [
                                'en-GB' => 'The customer discount group can be used via the ACRIS plugin to allow multiple customers with the same customer discount group to receive a discount.',
                                'de-DE' => 'Die Kunden-Rabattgruppe kann über das ACRIS Plugin verwendet werden, um mehreren Kunden mit derselben Kunden-Rabattgruppe einen Rabatt zu ermöglichen.'
                            ]
                        ]]
                ],
                'relations' => [
                    [
                        'entityName' => 'customer'
                    ]
                ]
            ]], $context);
        };

        if($customFieldSet->search((new Criteria())->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_PRODUCT_DISCOUNT_GROUP)), $context)->count() == 0) {
            $customFieldSet->create([[
                'name' => self::CUSTOM_FIELD_SET_NAME_PRODUCT_DISCOUNT_GROUP,
                'config' => [
                    'label' => [
                        'en-GB' => 'Merchandise group (discount group)',
                        'de-DE' => 'Warengruppe (Rabattgruppe)'
                    ]
                ],
                'customFields' => [
                    ['name' => 'acris_discount_group_product_value', 'type' => CustomFieldTypes::TEXT,
                        'config' => [
                            'componentName' => 'sw-field',
                            'type' => 'text',
                            'customFieldType' => 'text',
                            'customFieldPosition' => 1,
                            'label' => [
                                'en-GB' => 'Merchandise group (discount group)',
                                'de-DE' => 'Warengruppe (Rabattgruppe)'
                            ],
                            'helpText' => [
                                'en-GB' => 'The merchandise group (discount group) can be used via the ACRIS plugin to allow all products with the same merchandise group (discount group) to receive a discount.',
                                'de-DE' => 'Die Warengruppe (Rabattgruppe) kann über das ACRIS Plugin verwendet werden, um allen Produkten mit derselben Warengruppe (Rabattgruppe) einen Rabatt zu ermöglichen.'
                            ]
                        ]]
                ],
                'relations' => [
                    [
                        'entityName' => 'product'
                    ]
                ]
            ]], $context);
        };
    }

    private function removeCustomFields(Context $context, array $setNames): void
    {
        /* Check for snippets if they exist for custom fields */
        $this->checkForExistingCustomFieldSnippets($context);

        $customFieldSet = $this->container->get('custom_field_set.repository');
        foreach ($setNames as $setName) {
            $id = $customFieldSet->searchIds((new Criteria())->addFilter(new EqualsFilter('name', $setName)), $context)->firstId();
            if($id) $customFieldSet->delete([['id' => $id]], $context);
        }
    }

    private function checkForExistingCustomFieldSnippets(Context $context): void
    {
        /** @var EntityRepository $snippetRepository */
        $snippetRepository = $this->container->get('snippet.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('translationKey', 'customFields.' . 'acris_discount_group_value'),
            new EqualsFilter('translationKey', 'customFields.' . 'acris_discount_group_customer_value'),
            new EqualsFilter('translationKey', 'customFields.' . 'acris_discount_group_product_value'),
        ]));

        /** @var EntitySearchResult $searchResult */
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

    private function addImportExportProfile(Context $context): void
    {
        $importExportProfileRepository = $this->container->get('import_export_profile.repository');
        foreach ($this->getOptimizedSystemDefaultProfiles() as $profile) {
            $this->createIfNotExists($importExportProfileRepository, [['name' => 'name', 'value' => $profile['name']]], $profile, $context);
        }
    }

    private function getOptimizedSystemDefaultProfiles(): array
    {
        return [
            [
                'name' => self::IMPORT_EXPORT_PROFILE_NAME,
                'label' => self::IMPORT_EXPORT_PROFILE_NAME,
                'systemDefault' => true,
                'sourceEntity' => 'acris_discount_group',
                'fileType' => 'text/csv',
                'delimiter' => ';',
                'enclosure' => '"',
                'mapping' => [
                    ['key' => 'id', 'mappedKey' => 'id'],
                    ['key' => 'internalName', 'mappedKey' => 'internalName'],
                    ['key' => 'active', 'mappedKey' => 'active'],
                    ['key' => 'activeFrom', 'mappedKey' => 'activeFrom'],
                    ['key' => 'activeUntil', 'mappedKey' => 'activeUntil'],
                    ['key' => 'priority', 'mappedKey' => 'priority'],
                    ['key' => 'excluded', 'mappedKey' => 'excluded'],
                    ['key' => 'customerAssignmentType', 'mappedKey' => 'customerAssignmentType'],
                    ['key' => 'customerId', 'mappedKey' => 'customerId'],
                    ['key' => 'discountGroup', 'mappedKey' => 'categoryDiscountGroup'],
                    ['key' => 'rules', 'mappedKey' => 'ruleIds'],
                    ['key' => 'productAssignmentType', 'mappedKey' => 'productAssignmentType'],
                    ['key' => 'productId', 'mappedKey' => 'productId'],
                    ['key' => 'materialGroup', 'mappedKey' => 'productDiscountGroup'],
                    ['key' => 'productStreams', 'mappedKey' => 'productStreamIds'],
                    ['key' => 'discountType', 'mappedKey' => 'discountType'],
                    ['key' => 'discount', 'mappedKey' => 'discount'],
                    ['key' => 'calculationType', 'mappedKey' => 'calculationType'],
                    ['key' => 'listPriceType', 'mappedKey' => 'listPriceType'],
                ],
                'translations' => [
                    'en-GB' => [
                        'label' => self::IMPORT_EXPORT_PROFILE_NAME
                    ],
                    'de-DE' => [
                        'label' => self::IMPORT_EXPORT_PROFILE_NAME
                    ]
                ],
            ],
        ];
    }

    private function createIfNotExists(EntityRepository $repository, array $equalFields, array $data, Context $context): void
    {
        $filters = [];
        foreach ($equalFields as $equalField) {
            $filters[] = new EqualsFilter($equalField['name'], $equalField['value']);
        }
        if(sizeof($filters) > 1) {
            $filter = new MultiFilter(MultiFilter::CONNECTION_OR, $filters);
        } else {
            $filter = array_shift($filters);
        }

        $searchResult = $repository->search((new Criteria())->addFilter($filter), $context);
        if($searchResult->count() == 0) {
            $repository->create([$data], $context);
        }
    }

    private function cleanupImportExportProfile(Context $context): void
    {
        $importExportProfile = $this->container->get('import_export_profile.repository');
        $storeLocatorProfiles = $importExportProfile->search((new Criteria())->addFilter(new EqualsFilter('sourceEntity', 'acris_discount_group')), $context);
        $ids = [];

        if ($storeLocatorProfiles->getTotal() > 0 && $storeLocatorProfiles->first()) {
            /** @var ImportExportProfileEntity $entity */
            foreach ($storeLocatorProfiles->getEntities() as $entity) {
                if ($entity->getSystemDefault() === true) {
                    $importExportProfile->update([
                        ['id' => $entity->getId(), 'systemDefault' => false ]
                    ], $context);
                }
                $ids[] = ['id' => $entity->getId()];
            }
            $importExportProfile->delete($ids, $context);
        }
    }

    private function insertDefaultMailTemplate(Context $context): void
    {
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        $mailTemplateTypePartialDeliverySearchResult = $mailTemplateTypeRepository->searchIds((new Criteria())->addFilter(new EqualsFilter('technicalName', self::DEFAULT_MAIL_TEMPLATE_TYPE_DISCOUNT_GROUP)), $context);
        if(empty($mailTemplateTypePartialDeliverySearchResult->firstId())) {
            $mailTemplateTypePartialDeliveryId = Uuid::randomHex();
            $mailTemplatePartialDeliveryId = Uuid::randomHex();
            $mailTemplateTypePartialDeliveryData = [
                'id' => $mailTemplateTypePartialDeliveryId,
                'technicalName' => self::DEFAULT_MAIL_TEMPLATE_TYPE_DISCOUNT_GROUP,
                'availableEntities' => [ "order" => "order", "salesChannel" =>"sales_channel", "editOrderUrl" =>null ],
                'translations' => [
                    'de-DE' => [
                        'name' => 'ACRIS-Auftragsbestätigung mit Rabatten aus der Rabattgruppe'
                    ],
                    'en-GB' => [
                        'name' => 'ACRIS order confirmation with discounts from discount group'
                    ],
                    [
                        'name' => 'ACRIS order confirmation with discounts from discount group',
                        'languageId' => Defaults::LANGUAGE_SYSTEM
                    ]
                ],
                'mailTemplates' => [
                    [
                        'id' => $mailTemplatePartialDeliveryId,
                        'systemDefault' => true,
                        'translations' => [
                            'de-DE' => [
                                'senderName' => '{{ salesChannel.name }}',
                                'subject' => 'Auftragsbestätigung - Rabattgruppe',
                                'description' => 'Standard-E-Mail-Vorlage für Rabattgruppen.',
                                'contentHtml' => file_get_contents($this->path . '/Resources/mail-template/html/de-DE/discount-group.html.twig'),
                                'contentPlain' => 'Kein Inhalt.'
                            ],
                            'en-GB' => [
                                'senderName' => '{{ salesChannel.name }}',
                                'subject' => 'Order confirmation - Discount group',
                                'description' => 'Default E-mail template for discount groups.',
                                'contentHtml' => file_get_contents($this->path . '/Resources/mail-template/html/en-GB/discount-group.html.twig'),
                                'contentPlain' => 'No content.'
                            ],
                            [
                                'senderName' => '{{ salesChannel.name }}',
                                'subject' => 'Order confirmation - Discount group',
                                'description' => 'Default E-mail template for discount groups.',
                                'contentHtml' => file_get_contents($this->path . '/Resources/mail-template/html/en-GB/discount-group.html.twig'),
                                'contentPlain' => 'No content.',
                                'languageId' => Defaults::LANGUAGE_SYSTEM
                            ]
                        ],
                    ]
                ]
            ];
            $mailTemplateTypeRepository->upsert([$mailTemplateTypePartialDeliveryData], $context);
        }
    }

    private function removeDefaultMailTemplate(Context $context): void
    {
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');
        $mailTemplateTypePartialDeliverySearchResult = $mailTemplateTypeRepository->searchIds((new Criteria())->addFilter(new EqualsFilter('technicalName', self::DEFAULT_MAIL_TEMPLATE_TYPE_DISCOUNT_GROUP)), $context);
        if($mailTemplateTypePartialDeliverySearchResult->firstId()) {
            $mailTemplateRepository = $this->container->get('mail_template.repository');
            $mailTemplateSearchResult = $mailTemplateRepository->searchIds((new Criteria())->addFilter(new EqualsFilter('mailTemplateTypeId', $mailTemplateTypePartialDeliverySearchResult->firstId())), $context);
            $templateAssigned = false;
            foreach ($mailTemplateSearchResult->getIds() as $id) {
                $flowSequenceRepository = $this->container->get('flow_sequence.repository');
                $flowSequenceSearchResult = $flowSequenceRepository->searchIds((new Criteria())->addFilter(new EqualsFilter('config.mailTemplateId', $id)), $context);
                if($flowSequenceSearchResult->firstId() && $flowSequenceSearchResult->getTotal() > 0) $templateAssigned = true;
            }

            if ($templateAssigned !== true) {
                foreach ($mailTemplateSearchResult->getIds() as $id) {
                    $mailTemplateRepository->delete([['id'=>$id]], $context);
                }
                $mailTemplateTypeRepository->delete([['id'=>$mailTemplateTypePartialDeliverySearchResult->firstId()]], $context);
            }
        }
    }

    private function columnExists(Connection $connection, string $table, string $column): bool
    {
        $exists = $connection->fetchOne(
            'SHOW COLUMNS FROM `' . $table . '` WHERE `Field` LIKE :column',
            ['column' => $column]
        );

        return !empty($exists);
    }

    private function updateDefaultImportExportProfile(Context $context): void
    {
        $importExportProfileRepository = $this->container->get('import_export_profile.repository');
        foreach ($this->getOptimizedSystemDefaultProfiles() as $profile) {
            $this->updateIfExists($importExportProfileRepository, [['name' => 'name', 'value' => $profile['name']], ['name' => 'systemDefault', 'value' => true]], $profile, $context);
        }
    }

    private function updateIfExists(EntityRepository $repository, array $equalFields, array $data, Context $context): void
    {
        $filters = [];
        foreach ($equalFields as $equalField) {
            $filters[] = new EqualsFilter($equalField['name'], $equalField['value']);
        }
        if(sizeof($filters) > 1) {
            $filter = new MultiFilter(MultiFilter::CONNECTION_AND, $filters);
        } else {
            $filter = array_shift($filters);
        }

        $searchResult = $repository->search((new Criteria())->addFilter($filter), $context);
        foreach ($searchResult->getElements() as $element) {
            $data['id'] = $element->get('id');
            $repository->upsert([$data], $context);
        }
    }
}
