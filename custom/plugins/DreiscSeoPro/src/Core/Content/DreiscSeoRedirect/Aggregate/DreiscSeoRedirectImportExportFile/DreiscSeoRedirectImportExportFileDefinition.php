<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class DreiscSeoRedirectImportExportFileDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'dreisc_seo_redirect_import_export_file';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DreiscSeoRedirectImportExportFileCollection::class;
    }

    public function getEntityClass(): string
    {
        return DreiscSeoRedirectImportExportFileEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new Required(), new PrimaryKey()),
            (new StringField('original_name', 'originalName'))->setFlags(new Required()),
            (new StringField('path', 'path'))->setFlags(new Required()),
            (new DateTimeField('expire_date', 'expireDate'))->setFlags(new Required()),
            new IntField('size', 'size'),
            (new StringField('access_token', 'accessToken'))->setFlags(new Required()),
            (new StringField('activity', 'activity'))->setFlags(new Required()),
            (new StringField('state', 'state'))->setFlags(new Required()),
            (new IntField('records', 'records'))->setFlags(new Required()),
            (new JsonField('config', 'config', [], []))->setFlags(new Required())
        ]);
    }
}
