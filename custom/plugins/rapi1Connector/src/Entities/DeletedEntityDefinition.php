<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Entities;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class DeletedEntityDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'deleted_entity';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DeletedEntityCollection::class;
    }

    public function getEntityClass(): string
    {
        return DeletedEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
                (new IdField('entity_id', 'entityId'))->addFlags(new Required()),
                (new StringField('type', 'type'))->addFlags(new Required()),
                new UpdatedAtField(),
                new CreatedAtField(),
            ]
        );
    }
}