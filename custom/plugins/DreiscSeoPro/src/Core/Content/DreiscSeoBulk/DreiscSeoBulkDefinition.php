<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\Aggregate\DreiscSeoBulkTemplate\DreiscSeoBulkTemplateDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\CustomField\CustomFieldDefinition;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class DreiscSeoBulkDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'dreisc_seo_bulk';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DreiscSeoBulkCollection::class;
    }

    public function getEntityClass(): string
    {
        return DreiscSeoBulkEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new Required(), new PrimaryKey()),
            (new StringField('area', 'area'))->addFlags(new Required()),
            (new StringField('seo_option', 'seoOption'))->addFlags(new Required()),
            (new FkField('language_id', 'languageId', LanguageDefinition::class))->addFlags(new Required()),
            new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class),
            (new FkField('category_id', 'categoryId', CategoryDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(CategoryDefinition::class))->addFlags(new Required()),
            new FkField('dreisc_seo_bulk_template_id', 'dreiscSeoBulkTemplateId', DreiscSeoBulkTemplateDefinition::class),
            new IntField('priority', 'priority'),
            new StringField('overwrite', 'overwrite'),
            new FkField('overwrite_custom_field_id', 'overwriteCustomFieldId', CustomFieldDefinition::class),
            new BoolField('inherit', 'inherit'),

            new ManyToOneAssociationField('language', 'language_id', LanguageDefinition::class, 'id', true),
            (new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id', false))->addFlags(new CascadeDelete()),
            (new ManyToOneAssociationField('category', 'category_id', CategoryDefinition::class, 'id', false))->addFlags(new CascadeDelete()),
            new ManyToOneAssociationField('dreiscSeoBulkTemplate', 'dreisc_seo_bulk_template_id', DreiscSeoBulkTemplateDefinition::class, 'id', false),
            new ManyToOneAssociationField('overwriteCustomField', 'overwrite_custom_field_id', CustomFieldDefinition::class, 'id', false),
        ]);
    }
}
