<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1592834205AfterTableDreiscSeoRedirectImportExportLog extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1_592_834_205;
    }

    /**
     * @param Connection $connection
     */
    public function update(Connection $connection): void
	{
        $connection->executeStatement("
			ALTER TABLE `dreisc_seo_redirect_import_export_log`
			    ADD CONSTRAINT `json.dreisc_seo_redirect_import_export_log.row_value` CHECK (JSON_VALID(`row_value`)),
			ADD CONSTRAINT `json.dreisc_seo_redirect_import_export_log.errors` CHECK (JSON_VALID(`errors`)),
				ADD KEY `fk.dreisc_seo_redirect_import_export_log.dreisc_seo_redirect_id` (`dreisc_seo_redirect_id`),
				ADD CONSTRAINT `fk.dreisc_seo_redirect_import_export_log.dreisc_seo_redirect_id` FOREIGN KEY (`dreisc_seo_redirect_id`) REFERENCES `dreisc_seo_redirect` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
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
