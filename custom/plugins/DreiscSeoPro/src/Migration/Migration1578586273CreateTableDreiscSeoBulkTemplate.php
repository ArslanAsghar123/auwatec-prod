<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1578586273CreateTableDreiscSeoBulkTemplate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 1_578_586_273;
    }

    /**
     * @param Connection $connection
     */
    public function update(Connection $connection): void
	{
        $connection->executeStatement("
			CREATE TABLE IF NOT EXISTS `dreisc_seo_bulk_template` (
			    `id` BINARY(16) NOT NULL,
			    `area` VARCHAR(255) NOT NULL,
			    `seo_option` VARCHAR(255) NOT NULL,
			    `name` VARCHAR(255) NOT NULL,
			    `spaceless` TINYINT(1) NULL DEFAULT '0',
			    `template` LONGTEXT NULL,
			    `created_at` DATETIME(3) NOT NULL,
			    `updated_at` DATETIME(3) NULL,
			    PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
	}

    /**
     * @param Connection $connection
     */
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
