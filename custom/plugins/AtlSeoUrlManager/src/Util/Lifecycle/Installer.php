<?php declare(strict_types=1);

namespace Atl\SeoUrlManager\Util\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class Installer
{
    public const SEO_URL_MANAGER_PRODUCT_SET = 'atl_seo_url_manager_product';
    public const SEO_URL_MANAGER_PRODUCT_RELATION = 'atl_seo_url_manager_product_relation';
    public const SEO_URL_MANAGER_PRODUCT_BOX_LINK_INHERITANCE_OVERRIDE = 'atl_seo_url_manager_product_box_link_inheritance_override';
    public const SEO_URL_MANAGER_PRODUCT_CANONICAL_INHERITANCE_OVERRIDE = 'atl_seo_url_manager_product_canonical_inheritance_override';

    /**
     * @var EntityRepository
     */
    private EntityRepository $customFieldSetRepository;

    public function __construct(EntityRepository $customFieldSetRepository)
    {
        $this->customFieldSetRepository = $customFieldSetRepository;
    }

    public function install(Context $context): void
    {
        $this->createCustomFields($context);
    }

    private function createCustomFields(Context $context): void
    {
        $this->customFieldSetRepository->upsert([
            [
                'id' => Uuid::fromStringToHex(self::SEO_URL_MANAGER_PRODUCT_SET),
                'name' => self::SEO_URL_MANAGER_PRODUCT_SET,
                'config' => [
                    'label' => [
                        'en-GB' => 'Product (SEO URL Manager)',
                        'de-DE' => 'Produkt (SEO URL Manager)'
                    ],
                    'translated' => true
                ],
                'relations' => [
                    [
                        'id' => Uuid::fromStringToHex(self::SEO_URL_MANAGER_PRODUCT_RELATION),
                        'entityName' => 'product'
                    ]
                ],
                'customFields' => [
                    [
                        'id' => Uuid::fromStringToHex(self::SEO_URL_MANAGER_PRODUCT_BOX_LINK_INHERITANCE_OVERRIDE),
                        'name' => self::SEO_URL_MANAGER_PRODUCT_BOX_LINK_INHERITANCE_OVERRIDE,
                        'type' => CustomFieldTypes::BOOL,
                        'customFieldSetId' => Uuid::fromStringToHex(self::SEO_URL_MANAGER_PRODUCT_SET),
                        'config' => [
                            'type' => 'switch',
                            'label' => [
                                'en-GB' => 'Unlink product box on parent for this product',
                                'de-DE' => 'Produkt-Box-Verlinkung auf Parent für dieses Produkt aufheben'
                            ],
                            'helpText' => [
                                'en-GB' => 'With this option you can override the global app setting to link from product boxes to the parent for this product. <br><br> This automatically reverts to the setting you made under "Variants > Storefront presentation > Product listings".',
                                'de-DE' => 'Mit dieser Option kannst du die globale App-Einstellung, aus Produkt Boxen auf den Parent Artikel zu verlinken, für dieses Produkt aufheben. <br><br> Damit greift automatisch wieder die Einstellung, welche du unter "Varianten > Storefront-Darstellung > Produktliste" vornimmst.'
                            ],
                            'componentName' => 'sw-field',
                            'customFieldType' => 'switch',
                            'customFieldPosition' => 1
                        ]
                    ],
                    [
                        'id' => Uuid::fromStringToHex(self::SEO_URL_MANAGER_PRODUCT_CANONICAL_INHERITANCE_OVERRIDE),
                        'name' => self::SEO_URL_MANAGER_PRODUCT_CANONICAL_INHERITANCE_OVERRIDE,
                        'type' => CustomFieldTypes::BOOL,
                        'customFieldSetId' => Uuid::fromStringToHex(self::SEO_URL_MANAGER_PRODUCT_SET),
                        'config' => [
                            'type' => 'switch',
                            'label' => [
                                'en-GB' => 'Unlink parent canonical URL for variants for this product',
                                'de-DE' => 'Parent Canonical-URL bei Varianten für dieses Produkt aufheben'
                            ],
                            'helpText' => [
                                'en-GB' => 'This option allows you to override the global app setting to use the parent canonical URL on variants for this product. <br><br> This automatically makes the canonical URL the URL of the respective variant again. In addition, the setting you made under "SEO > Use single canonical URL for all variants" also applies again.',
                                'de-DE' => 'Mit dieser Option kannst du die globale App-Einstellung, die Parent Canonical-URL bei Varianten zu verwenden, für dieses Produkt aufheben. <br><br> Damit ist die Canonical-URL automatisch wieder die URL der jeweiligen Variante. Zusätzlich greift auch wieder die Einstellung, welche du unter "SEO > Verwende dieselbe Canonical-URL für alle Varianten" vornimmst.'
                            ],
                            'componentName' => 'sw-field',
                            'customFieldType' => 'switch',
                            'customFieldPosition' => 2
                        ]
                    ]
                ]
            ]
        ], $context);
    }
}
