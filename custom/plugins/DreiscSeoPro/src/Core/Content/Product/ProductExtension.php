<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Product;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * Adds new fields to the entity.
 *
 * To load the field, the association must be loaded before the search:
 * $repository->search(
 *   (new Criteria([ $entityId ]))
 *       ->addAssociation('dreiscSeoRedirects')
 * );
 *
 * Then you have access via:
 * $entity->getExtension('dreiscSeoRedirects');
 */
class ProductExtension extends EntityExtension
{
    final public const DREISC_SEO_REDIRECTS_SOURCE_ASSOCIATION = 'dreiscSeoRedirectSource';
    final public const DREISC_SEO_REDIRECTS_REDIRECT_ASSOCIATION = 'dreiscSeoRedirects';

    /**
    * @param FieldCollection $collection
    */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                self::DREISC_SEO_REDIRECTS_SOURCE_ASSOCIATION,
                DreiscSeoRedirectDefinition::class,
                'source_product_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                self::DREISC_SEO_REDIRECTS_REDIRECT_ASSOCIATION,
                DreiscSeoRedirectDefinition::class,
                'redirect_product_id'
            )
        );
    }

    /**
     * @return string
     */
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}

