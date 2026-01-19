<?php declare(strict_types=1);

namespace Mill\ProductDownloadsTab;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class MillProductDownloadsTab extends Plugin
{

    public function install(InstallContext $installContext): void
    {

        /**
         * install only if custom field set is not available
         */

        $customFieldSet = $this->getCustomFieldSet();

        $context = $installContext->getContext();

        $ids = $this->getCustomFieldSetIds($customFieldSet, $context);

        if (empty($ids)) {

            /**
             * @var EntityRepository $customFieldSetRepository
             */

            $customFieldSetRepository = $this->container->get('custom_field_set.repository');

            $customFieldSetRepository->create($customFieldSet, $context);

        }

    }

    /**
     * {@inheritDoc}
     *
     * @param UninstallContext $uninstallContext
     * @throws InconsistentCriteriaIdsException
     */

    public function uninstall(UninstallContext $uninstallContext): void
    {

        if (!$uninstallContext->keepUserData()) {

            $this->deleteCustomFieldSet($uninstallContext->getContext());

            $this->deleteCustomFields($uninstallContext->getContext());

        }

    }

    /**
     * Helper-function to delete the advantage custom field set
     *
     * @param Context $context
     *
     * @throws InconsistentCriteriaIdsException
     */

    protected function deleteCustomFieldSet($context): void
    {

        /**
         * @var EntityRepository $customFieldSetRepository
         */

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter('name', 'mill_product_downloads')
        );

        $customFieldSetId = $customFieldSetRepository->searchIds($criteria, $context)->firstId();

        if (!empty($customFieldSetId)) {

            $customFieldSetRepository->delete(
                [
                    [
                        'id' => $customFieldSetId
                    ]
                ],
                $context
            );

        }

    }

    /**
     * Helper-function to delete all relevant plugin custom fields
     *
     * @param Context $context
     *
     * @throws InconsistentCriteriaIdsException
     */

    protected function deleteCustomFields($context): void
    {

        /**
         * @var EntityRepository $customFieldRepository
         */

        $customFieldRepository = $this->container->get('custom_field.repository');

        $criteria = new Criteria();

        $criteria->addFilter(
            new ContainsFilter('name', 'mill_product_download')
        );

        $ids = $customFieldRepository->searchIds($criteria, $context)->getIds();

        if (!empty($ids)) {

            $data = [];

            foreach ($ids as $id) {

                $data[] = [
                    'id' => $id
                ];

            }

            $customFieldRepository->delete($data, $context);

        }

    }

    /**
     * Helper-function to get custom field set ids
     *
     * @param $customFieldSets
     * @param $context
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     */

    private function getCustomFieldSetIds($customFieldSets, $context): array
    {

        /**
         * @var EntityRepository $customFieldSetRepository
         */

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $ids = [];

        foreach ($customFieldSets as $customFieldSet) {

            $criteria = new Criteria();

            $criteria->addFilter(
                new EqualsFilter('name', $customFieldSet['name'])
            );

            $customFieldSetId = $customFieldSetRepository->searchIds($criteria, $context)->firstId();

            if (!empty($customFieldSetId)) {

                $ids[] = [
                    'id' => $customFieldSetId
                ];

            }

        }

        return $ids;

    }

    /**
     * Helper-function to get the required custom field set with it's fields
     *
     * @return array
     */

    protected function getCustomFieldSet(): array
    {

        return [
            [
                'name' => 'mill_product_downloads',
                'config' => [
                    'label' => [
                        'de-DE' => 'Downloads',
                        'en-GB' => 'Downloads',
                    ],
                ],
                'customFields' => [
                    [
                        'name' => 'mill_product_download_1',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 1,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 1',
                                'en-GB' => 'Download 1',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_2',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 2,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 2',
                                'en-GB' => 'Download 2',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_3',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 3,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 3',
                                'en-GB' => 'Download 3',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_4',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 4,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 4',
                                'en-GB' => 'Download 4',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_5',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 5,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 5',
                                'en-GB' => 'Download 5',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_6',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 6,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 6',
                                'en-GB' => 'Download 6',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_7',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 7,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 7',
                                'en-GB' => 'Download 7',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_8',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 8,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 8',
                                'en-GB' => 'Download 8',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_9',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 9,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 9',
                                'en-GB' => 'Download 9',
                            ],
                        ],
                    ],
                    [
                        'name' => 'mill_product_download_10',
                        'type' => CustomFieldTypes::MEDIA,
                        'config' => [
                            'customFieldPosition' => 10,
                            'componentName' => 'sw-media-field',
                            'customFieldType' => CustomFieldTypes::MEDIA,
                            'label' => [
                                'de-DE' => 'Download 10',
                                'en-GB' => 'Download 10',
                            ],
                        ],
                    ]
                ],
                'relations' => [
                    [
                        'entityName' =>  $this->container->get(ProductDefinition::class)->getEntityName()
                    ]
                ]
            ]
        ];

    }

}