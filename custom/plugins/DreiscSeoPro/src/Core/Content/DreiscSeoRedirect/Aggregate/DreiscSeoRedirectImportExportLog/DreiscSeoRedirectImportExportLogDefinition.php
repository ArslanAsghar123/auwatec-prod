<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportLog;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class DreiscSeoRedirectImportExportLogDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'dreisc_seo_redirect_import_export_log';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DreiscSeoRedirectImportExportLogCollection::class;
    }

    public function getEntityClass(): string
    {
        return DreiscSeoRedirectImportExportLogEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new Required(), new PrimaryKey()),
            (new FkField(
                'dreisc_seo_redirect_id',
                'dreiscSeoRedirectId',
                DreiscSeoRedirectDefinition::class
            )),
            (new IntField('row_index', 'rowIndex')),
            (new JsonField('row_value', 'rowValue')),
            (new JsonField('errors', 'errors')),

            (new ManyToOneAssociationField(
                'dreiscSeoRedirect',
                'dreisc_seo_redirect_id',
                DreiscSeoRedirectDefinition::class
            ))->addFlags(
                new CascadeDelete()
            )
        ]);
    }
}
