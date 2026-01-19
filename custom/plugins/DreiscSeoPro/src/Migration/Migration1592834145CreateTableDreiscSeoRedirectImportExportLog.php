<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1592834145CreateTableDreiscSeoRedirectImportExportLog extends MigrationStep
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
			CREATE TABLE IF NOT EXISTS `dreisc_seo_redirect_import_export_log` (
			    `id` BINARY(16) NOT NULL,
			    `dreisc_seo_redirect_id` BINARY(16) NULL,
			    `row_index` INT(11) NULL,
			    `row_value` JSON NULL,
			    `errors` JSON NULL,
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
