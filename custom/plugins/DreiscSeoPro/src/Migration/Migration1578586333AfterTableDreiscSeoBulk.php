<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1578586333AfterTableDreiscSeoBulk extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 1_578_586_333;
    }

    /**
     * @param Connection $connection
     */
    public function update(Connection $connection): void
	{
	    /** Try block to support the --keep-user-data uninstall */
	    try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_bulk`
                        ADD KEY `fk.dreisc_seo_bulk.language_id` (`language_id`),
                    ADD KEY `fk.dreisc_seo_bulk.sales_channel_id` (`sales_channel_id`),
                    ADD KEY `fk.dreisc_seo_bulk.category_id` (`category_id`,`category_version_id`),
                    ADD KEY `fk.dreisc_seo_bulk.dreisc_seo_bulk_template_id` (`dreisc_seo_bulk_template_id`),
                    ADD CONSTRAINT `fk.dreisc_seo_bulk.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.dreisc_seo_bulk.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.dreisc_seo_bulk.category_id` FOREIGN KEY (`category_id`,`category_version_id`) REFERENCES `category` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.dreisc_seo_bulk.dreisc_seo_bulk_template_id` FOREIGN KEY (`dreisc_seo_bulk_template_id`) REFERENCES `dreisc_seo_bulk_template` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
            ");
        } catch (\Exception) { }
	}

    /**
     * @param Connection $connection
     */
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
