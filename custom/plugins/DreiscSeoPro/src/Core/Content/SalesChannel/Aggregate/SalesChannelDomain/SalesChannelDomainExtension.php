<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\SalesChannel\Aggregate\SalesChannelDomain;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;

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
class SalesChannelDomainExtension extends EntityExtension
{
    final public const DREISC_SEO_REDIRECTS_SOURCE_ASSOCIATION = 'dreiscSeoRedirectSources';
    final public const DREISC_SEO_REDIRECTS_REDIRECT_ASSOCIATION = 'dreiscSeoRedirectsRedirects';

    /**
    * @param FieldCollection $collection
    */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                self::DREISC_SEO_REDIRECTS_SOURCE_ASSOCIATION,
                DreiscSeoRedirectDefinition::class,
                'source_sales_channel_domain_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                self::DREISC_SEO_REDIRECTS_REDIRECT_ASSOCIATION,
                DreiscSeoRedirectDefinition::class,
                'redirect_sales_channel_domain_id'
            )
        );
    }

    /**
     * @return string
     */
    public function getDefinitionClass(): string
    {
        return SalesChannelDomainDefinition::class;
    }
}

