<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Category;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkDefinition;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
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
class CategoryExtension extends EntityExtension
{
    final public const DREISC_SEO_REDIRECTS_SOURCE_ASSOCIATION = 'dreiscSeoRedirectSource';
    final public const DREISC_SEO_REDIRECTS_REDIRECT_ASSOCIATION = 'dreiscSeoRedirects';
    final public const DREISC_SEO_BULK_ASSOCIATION = 'dreiscSeoBulks';

    /**
    * @param FieldCollection $collection
    */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                self::DREISC_SEO_REDIRECTS_SOURCE_ASSOCIATION,
                DreiscSeoRedirectDefinition::class,
                'source_category_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                self::DREISC_SEO_REDIRECTS_REDIRECT_ASSOCIATION,
                DreiscSeoRedirectDefinition::class,
                'redirect_category_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                self::DREISC_SEO_BULK_ASSOCIATION,
                DreiscSeoBulkDefinition::class,
                'category_id'
            )
        );
    }

    /**
     * @return string
     */
    public function getDefinitionClass(): string
    {
        return CategoryDefinition::class;
    }
}

