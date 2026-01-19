<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk\Aggregate\DreiscSeoBulkTemplate;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class DreiscSeoBulkTemplateDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'dreisc_seo_bulk_template';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DreiscSeoBulkTemplateCollection::class;
    }

    public function getEntityClass(): string
    {
        return DreiscSeoBulkTemplateEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new Required(), new PrimaryKey()),
            (new StringField('area', 'area'))->addFlags(new Required()),
            (new StringField('seo_option', 'seoOption'))->addFlags(new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            new BoolField('spaceless', 'spaceless'),
            new BoolField('ai_prompt', 'aiPrompt'),
            (new LongTextField('template', 'template'))->addFlags(new AllowHtml(false)),

            new OneToManyAssociationField(
                'dreiscSeoBulks',
                DreiscSeoBulkDefinition::class,
                'dreisc_seo_bulk_template_id'
            )
        ]);
    }
}
