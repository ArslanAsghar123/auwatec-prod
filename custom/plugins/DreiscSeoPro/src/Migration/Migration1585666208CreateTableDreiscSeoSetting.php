<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1585666208CreateTableDreiscSeoSetting extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 1_585_666_208;
    }

    /**
     * @param Connection $connection
     */
    public function update(Connection $connection): void
	{
        $connection->executeStatement("
			CREATE TABLE IF NOT EXISTS `dreisc_seo_setting` (
			    `id` BINARY(16) NOT NULL,
			    `key` VARCHAR(255) NOT NULL,
			    `value` JSON NOT NULL,
			    `sales_channel_id` BINARY(16) NULL,
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
