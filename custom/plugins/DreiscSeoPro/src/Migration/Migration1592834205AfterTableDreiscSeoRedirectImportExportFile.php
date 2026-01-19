<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1592834205AfterTableDreiscSeoRedirectImportExportFile extends MigrationStep
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
			ALTER TABLE `dreisc_seo_redirect_import_export_file`
			    ADD CONSTRAINT `json.dreisc_seo_redirect_import_export_file.config` CHECK (JSON_VALID(`config`));
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
