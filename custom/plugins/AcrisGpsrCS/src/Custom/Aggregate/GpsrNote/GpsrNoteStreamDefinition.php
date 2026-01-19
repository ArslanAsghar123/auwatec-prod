<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\GpsrNote;

use Acris\Gpsr\Custom\GpsrNoteDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class GpsrNoteStreamDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'acris_gpsr_note_stream';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('gpsr_note_id', 'gpsrNoteId', GpsrNoteDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('gpsrNote', 'gpsr_note_id', GpsrNoteDefinition::class),
            new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class),
            new CreatedAtField()
        ]);
    }
}
