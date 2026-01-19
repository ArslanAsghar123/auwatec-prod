<?php declare(strict_types=1);

namespace Intedia\Doofinder\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class DooFinderLayerDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'intedia_doofinder_layer';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DooFinderLayerCollection::class;
    }

    public function getEntityClass(): string
    {
        return DooFinderLayerEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),

            (new IdField('doofinder_channel_id', 'doofinderChannelId',)),
            (new StringField('doofinder_hash_id', 'doofinderHashId')),
            (new StringField('doofinder_store_id', 'doofinderStoreId')),

            (new IdField('domain_id', 'domainId')),
            (new StringField('trigger', 'trigger')),
            (new StringField('name', 'name')),
            (new StringField('status', 'status')),
            (new StringField('status_message', 'statusMessage')),
            (new StringField('status_date', 'statusDate')),
            (new StringField('status_received_date', 'statusReceivedDate')),
        ]);
    }
}
