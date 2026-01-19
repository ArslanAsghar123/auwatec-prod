<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\SalesChannel;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

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
class SalesChannelExtension extends EntityExtension
{
    final public const DREISC_SEO_BULK_ASSOCIATION = 'dreiscSeoBulks';

    /**
    * @param FieldCollection $collection
    */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                self::DREISC_SEO_BULK_ASSOCIATION,
                DreiscSeoBulkDefinition::class,
                'sales_channel_id'
            )
        );
    }

    /**
     * @return string
     */
    public function getDefinitionClass(): string
    {
        return SalesChannelDefinition::class;
    }
}

