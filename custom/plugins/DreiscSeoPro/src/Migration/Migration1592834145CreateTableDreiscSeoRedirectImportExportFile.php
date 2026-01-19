<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1592834145CreateTableDreiscSeoRedirectImportExportFile extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1_592_834_145;
    }

    /**
     * @param Connection $connection
     */
    public function update(Connection $connection): void
	{
        $connection->executeStatement("
			CREATE TABLE IF NOT EXISTS `dreisc_seo_redirect_import_export_file` (
			    `id` BINARY(16) NOT NULL,
			    `original_name` VARCHAR(255) NOT NULL,
			    `path` VARCHAR(255) NOT NULL,
			    `expire_date` datetime(3) NOT NULL,
			    `size` INT(11) NULL,
			    `access_token` VARCHAR(255) NOT NULL,
			    `activity` VARCHAR(255) NOT NULL,
			    `state` VARCHAR(255) NOT NULL,
			    `records` INT(11) NOT NULL,
			    `config` JSON NOT NULL,
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
