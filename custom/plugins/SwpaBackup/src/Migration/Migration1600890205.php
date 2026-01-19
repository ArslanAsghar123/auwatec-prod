<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1600890205 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1600890205;
    }
    
    public function update(Connection $connection): void
    {
        $connection->executeUpdate('ALTER TABLE `swpa_backup` ADD COLUMN `time` FLOAT NULL DEFAULT NULL AFTER `comment`;');
    }
    
    public function updateDestructive(Connection $connection): void
    {
    
    }
}
