<?php

declare(strict_types=1);

namespace Bfn\DirectDebit\Installer;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomFieldInstaller implements InstallerInterface
{
    /** @var ContainerInterface */
    protected $container;
    /** @var EntityRepository */
    protected $orderRepository;
    /** @var EntityRepository */
    protected $customerRepository;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container,
        EntityRepository $orderRepository,
        EntityRepository $customerRepository
    )
    {
        $this->container            = $container;
        $this->orderRepository      = $orderRepository;
        $this->customerRepository   = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context): void
    {
        $this->createCustomFieldSetForOrderEntity($context);
        $this->createCustomFieldSetForCustomerEntity($context);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context): void
    {
        $currentVersion = $context->getCurrentPluginVersion();
        $targetVersion = $context->getUpdatePluginVersion();

        // Check if updating to version 1.2.0
        if (version_compare($currentVersion, '1.2.0', '<') && version_compare($targetVersion, '1.2.0', '>=')) {
            $this->addCustomFields($context->getContext());
        }

        // Check if updating to version 1.4.0
        if (version_compare($currentVersion, '1.4.0', '<') && version_compare($targetVersion, '1.4.0', '>=')) {
            $this->changeCustomFieldAccessibility($context->getContext());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context): void
    {
        $this->deleteCustomFields($context);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context): void
    {
        // Nothing to do here
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
        // Nothing to do here
    }

    /**
     * @param InstallContext $context
     * @return void
     */
    private function createCustomFieldSetForOrderEntity(InstallContext $context): void
    {
        $customFieldSetId = Uuid::randomHex();
        $orderCustomFiled = $this->checkCustomField($context->getContext(), 'bfn_order_direct_debit');

        if ($orderCustomFiled === false) {
            $this->container
                ->get('custom_field_set.repository')
                ->create([
                    [
                        'id' => $customFieldSetId,
                        'name' => 'bfn_order_direct_debit',
                        'config' => [
                            'label' => [
                                'de-DE' => 'SEPA (Bestellung)',
                                'en-GB' => 'Direct Debit (Order)',
                            ],
                            'entityName' => 'order',
                        ],
                        'relations' => [
                            [
                                'entityName' => 'order',
                                'referenceField' => 'id',
                                'localField' => 'id',
                                'cascade' => true,
                            ],
                        ],
                        'customFields' => [
                            [
                                'name' => 'bfn_order_direct_debit_owner',
                                'type' => 'text',
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Inhaber',
                                        'en-GB' => 'Owner',
                                    ],
                                    'readOnly' => false,
                                ],
                            ],
                            [
                                'name' => 'bfn_order_direct_debit_iban',
                                'type' => 'text',
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'IBAN',
                                        'en-GB' => 'IBAN',
                                    ],
                                    'readOnly' => false,
                                ],
                            ],
                            [
                                'name' => 'bfn_order_direct_debit_bic_swift',
                                'type' => 'text',
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'BIC',
                                        'en-GB' => 'BIC',
                                    ],
                                    'readOnly' => false,
                                ],
                            ],
                            [
                                'name' => 'bfn_order_direct_debit_mandate',
                                'type' => CustomFieldTypes::BOOL,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'The customer has accepted the mandate',
                                        'en-GB' => 'Der Kunde hat das Mandat angenommen'
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'switch',
                                    'type' => 'switch',
                                    'readOnly' => false,
                                ]
                            ],
                        ],
                    ],
                ], $context->getContext());
        }
    }

    /**
     * @param InstallContext $context
     * @return void
     */
    private function createCustomFieldSetForCustomerEntity(InstallContext $context): void
    {
        $customFieldSetId = Uuid::randomHex();
        $customerCustomFiled = $this->checkCustomField($context->getContext(), 'bfn_customer_direct_debit');

        if ($customerCustomFiled === false) {
            $this->container
                ->get('custom_field_set.repository')
                ->create([
                    [
                        'id' => $customFieldSetId,
                        'name' => 'bfn_customer_direct_debit',
                        'config' => [
                            'label' => [
                                'de-DE' => 'SEPA (Kunde)',
                                'en-GB' => 'Direct Debit (Customer)',
                            ],
                            'entityName' => 'customer',
                        ],
                        'relations' => [
                            [
                                'entityName' => 'customer',
                                'referenceField' => 'id',
                                'localField' => 'id',
                                'cascade' => true,
                            ],
                        ],
                        'customFields' => [
                            [
                                'name' => 'bfn_customer_direct_debit_owner',
                                'type' => 'text',
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'Inhaber',
                                        'en-GB' => 'Owner',
                                    ],
                                    'readOnly' => false,
                                ],
                            ],
                            [
                                'name' => 'bfn_customer_direct_debit_iban',
                                'type' => 'text',
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'IBAN',
                                        'en-GB' => 'IBAN',
                                    ],
                                    'readOnly' => false,
                                ],
                            ],
                            [
                                'name' => 'bfn_customer_direct_debit_bic_swift',
                                'type' => 'text',
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'BIC',
                                        'en-GB' => 'BIC',
                                    ],
                                    'readOnly' => false,
                                ],
                            ],
                            [
                                'name' => 'bfn_customer_direct_debit_mandate',
                                'type' => CustomFieldTypes::BOOL,
                                'config' => [
                                    'label' => [
                                        'de-DE' => 'The customer has accepted the mandate',
                                        'en-GB' => 'Der Kunde hat das Mandat angenommen'
                                    ],
                                    'componentName' => 'sw-field',
                                    'customFieldType' => 'switch',
                                    'type' => 'switch',
                                    'readOnly' => false,
                                ]
                            ],
                        ],
                    ],
                ], $context->getContext());
        }
    }

    /**
     * @param UninstallContext $context
     * @return void
     */
    private function deleteCustomFields(UninstallContext $context): void
    {

        $this->removeCustomFieldDataFromCustomerEntities($context->getContext());
        $this->removeCustomFieldDataFromOrderEntities($context->getContext());

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $criteria = new Criteria();
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR,
                [
                    new EqualsFilter('name', 'bfn_order_direct_debit'),
                    new EqualsFilter('name', 'bfn_customer_direct_debit'),
                ]
            )
        );

        $customFieldSetSearchResult = $customFieldSetRepository->search($criteria, $context->getContext());

        $customFieldSets = $customFieldSetSearchResult->getEntities();

        foreach ($customFieldSets as $customFieldSet) {
            $customFieldSetRepository->delete([['id' => $customFieldSet->getId()]], $context->getContext());
        }
    }

    /**
     * @param Context $context
     * @return void
     */
    private function removeCustomFieldDataFromOrderEntities(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addAssociation('customFields');

        $orders = $this->orderRepository->search($criteria, $context)->getEntities();

        foreach ($orders as $order) {
            $orderId = $order->getId();
            $customFields = $order->getCustomFields();

            // Remove the custom fields and the associated data
            unset(
                $customFields['bfn_order_direct_debit_owner'],
                $customFields['bfn_order_direct_debit_iban'],
                $customFields['bfn_order_direct_debit_bic_swift']
            );

            $this->orderRepository->update([
                [
                    'id' => $orderId,
                    'customFields' => $customFields,
                ],
            ], $context);
        }
    }

    /**
     * @param Context $context
     * @return void
     */
    private function removeCustomFieldDataFromCustomerEntities(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addAssociation('customFields');

        $customers = $this->customerRepository->search($criteria, $context)->getEntities();

        foreach ($customers as $customer) {
            $customerId = $customer->getId();
            $customFields = $customer->getCustomFields();

            // Remove the custom fields and the associated data
            unset(
                $customFields['bfn_customer_direct_debit_owner'],
                $customFields['bfn_customer_direct_debit_iban'],
                $customFields['bfn_customer_direct_debit_bic_swift']
            );

            $this->customerRepository->update([
                [
                    'id' => $customerId,
                    'customFields' => $customFields,
                ],
            ], $context);
        }
    }

    /**
     * @param $context
     * @param $field
     * @return bool
     */
    private function checkCustomField(Context $context, string $field): bool
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $field));
        $result = $customFieldSetRepository->searchIds($criteria, $context);

        if ($result->getTotal() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function addCustomFields(Context $context)
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $criteriaOne = new Criteria();
        $criteriaOne->addFilter(new EqualsFilter('name', 'bfn_customer_direct_debit'));
        $resultOne = $customFieldSetRepository->searchIds($criteriaOne, $context)->firstId();

        if ($resultOne) {
            $customFieldRepository = $this->container->get('custom_field.repository');
            $customFieldCriteriaOne = new Criteria();
            $customFieldCriteriaOne->addFilter(new EqualsFilter('name', 'bfn_customer_direct_debit_mandate'));
            $existingFieldOne = $customFieldRepository->search($customFieldCriteriaOne, $context)->first();

            if (!$existingFieldOne) {
                $customFieldRepository->upsert([
                    [
                        'id' => Uuid::randomHex(),
                        'name' => 'bfn_customer_direct_debit_mandate',
                        'type' => CustomFieldTypes::BOOL,
                        'config' => [
                            'label' => [
                                'de-DE' => 'The customer has accepted the mandate',
                                'en-GB' => 'Der Kunde hat das Mandat angenommen'
                            ],
                            'componentName' => 'sw-field',
                            'customFieldType' => 'switch',
                            'type' => 'switch',
                        ],
                        'customFieldSetId' => $resultOne,
                    ]
                ], $context);
            }
        }

        $criteriaTwo = new Criteria();
        $criteriaTwo->addFilter(new EqualsFilter('name', 'bfn_order_direct_debit'));
        $resultTwo = $customFieldSetRepository->searchIds($criteriaTwo, $context)->firstId();

        if ($resultTwo) {
            $customFieldRepository = $this->container->get('custom_field.repository');
            $customFieldCriteriaTwo = new Criteria();
            $customFieldCriteriaTwo->addFilter(new EqualsFilter('name', 'bfn_order_direct_debit_mandate'));
            $existingFieldTwo = $customFieldRepository->search($customFieldCriteriaTwo, $context)->first();

            if (!$existingFieldTwo) {
                $customFieldRepository->upsert([
                    [
                        'id' => Uuid::randomHex(),
                        'name' => 'bfn_order_direct_debit_mandate',
                        'type' => CustomFieldTypes::BOOL,
                        'config' => [
                            'label' => [
                                'de-DE' => 'The customer has accepted the mandate',
                                'en-GB' => 'Der Kunde hat das Mandat angenommen'
                            ],
                            'componentName' => 'sw-field',
                            'customFieldType' => 'switch',
                            'type' => 'switch',
                            'readOnly' => true,
                        ],
                        'customFieldSetId' => $resultTwo,
                    ]
                ], $context);
            }
        }
    }

    private function changeCustomFieldAccessibility(Context $context)
    {
        $customFieldRepository = $this->container->get('custom_field.repository');

        $orderCustomFieldSetName = 'bfn_order_direct_debit';
        $orderCustomFields = $this->getCustomFieldsBySetName($orderCustomFieldSetName, $context);

        foreach ($orderCustomFields as $orderCustomField) {
            $newConfig = $orderCustomField['config'];
            $newConfig['readOnly'] = false;

            $customFieldRepository->update([
                [
                    'id' => $orderCustomField['id'],
                    'config' => $newConfig,
                ],
            ], $context);
        }

        $customerCustomFieldSetName = 'bfn_customer_direct_debit';
        $customerCustomFields = $this->getCustomFieldsBySetName($customerCustomFieldSetName, $context);

        foreach ($customerCustomFields as $customerCustomField) {
            $newConfig = $customerCustomField['config'];
            $newConfig['readOnly'] = false;

            $customFieldRepository->update([
                [
                    'id' => $customerCustomField['id'],
                    'config' => $newConfig,
                ],
            ], $context);
        }
    }

    private function getCustomFieldsBySetName(string $setName, Context $context): array
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $setName));
        $criteria->addAssociation('customFields');

        $customFieldSet = $customFieldSetRepository->search($criteria, $context)->first();

        if (!$customFieldSet) {
            return [];
        }

        $customFields = [];

        foreach ($customFieldSet->getCustomFields()->getElements() as $customField) {
            $customFields[] = [
                'id' => $customField->getId(),
                'config' => $customField->getConfig(),
            ];
        }

        return $customFields;
    }
}