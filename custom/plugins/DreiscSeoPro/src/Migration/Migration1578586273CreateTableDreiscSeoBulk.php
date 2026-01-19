<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1578586273CreateTableDreiscSeoBulk extends MigrationStep
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
			CREATE TABLE IF NOT EXISTS `dreisc_seo_bulk` (
			    `id` BINARY(16) NOT NULL,
			    `area` VARCHAR(255) NOT NULL,
			    `seo_option` VARCHAR(255) NOT NULL,
			    `language_id` BINARY(16) NOT NULL,
			    `sales_channel_id` BINARY(16) NULL,
			    `category_id` BINARY(16) NOT NULL,
			    `category_version_id` BINARY(16) NOT NULL,
			    `dreisc_seo_bulk_template_id` BINARY(16) NULL,
			    `priority` INT(11) NULL,
			    `overwrite` TINYINT(1) NULL DEFAULT '0',
			    `inherit` TINYINT(1) NULL DEFAULT '0',
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
