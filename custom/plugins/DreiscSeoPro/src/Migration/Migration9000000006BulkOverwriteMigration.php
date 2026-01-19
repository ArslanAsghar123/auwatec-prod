<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration9000000006BulkOverwriteMigration extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 9_000_000_006;
    }

    public function update(Connection $connection): void
    {
        try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_bulk`
                CHANGE `overwrite` `overwrite_old` TINYINT(1) DEFAULT '0';
            ");
        } catch (\Exception) { }

        try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_bulk`
                ADD `overwrite` VARCHAR(50) AFTER `overwrite_old`;
            ");
        } catch (\Exception) { }

        try {
            $connection->executeStatement("
                UPDATE `dreisc_seo_bulk`
                SET `overwrite` = '" . DreiscSeoBulkEnum::OVERWRITE__ALWAYS . "'
                WHERE `overwrite_old` = 1;
            ");
        } catch (\Exception) { }

        try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_bulk`
                DROP COLUMN `overwrite_old`;
            ");
        } catch (\Exception) { }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
