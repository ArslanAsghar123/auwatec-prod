<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\DAL\Backup;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * Backup entity definition
 *
 * @package   Swpa\SwpaBackup\DAL\Backup
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class SwpaBackupDefinition extends EntityDefinition
{
    
    public const ENTITY_NAME = 'swpa_backup';
    
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        
        return self::ENTITY_NAME;
    }
    
    /**
     * @return string
     */
    public function getCollectionClass(): string
    {
        
        return SwpaBackupCollection::class;
    }
    
    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        
        return SwpaBackupEntity::class;
    }
    
    /**
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
                new IntField('status', 'status'),
                new StringField('filename', 'filename', 255),
                new StringField('filesystem', 'filesystem', 10),
                new StringField('comment', 'comment', 255),
                new IntField('deleted', 'deleted'),
                new FloatField('time', 'time'),
            ]
        );
    }
}
