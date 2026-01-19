<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1578586273CreateTableDreiscSeoRedirect extends MigrationStep
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
			CREATE TABLE IF NOT EXISTS `dreisc_seo_redirect` (
			    `id` BINARY(16) NOT NULL,
			    `active` TINYINT(1) NULL DEFAULT '0',
			    `redirect_http_status_code` VARCHAR(255) NULL,
			    `source_type` VARCHAR(255) NULL,
			    `has_source_sales_channel_domain_restriction` TINYINT(1) NULL DEFAULT '0',
			    `source_sales_channel_domain_restriction_ids` JSON NULL,
			    `source_sales_channel_domain_id` BINARY(16) NULL,
			    `source_path` LONGTEXT NULL,
			    `source_product_id` BINARY(16) NULL,
			    `source_product_version_id` BINARY(16) NOT NULL,
			    `source_category_id` BINARY(16) NULL,
			    `source_category_version_id` BINARY(16) NOT NULL,
			    `redirect_type` VARCHAR(255) NULL,
			    `redirect_url` LONGTEXT NULL,
			    `redirect_sales_channel_domain_id` BINARY(16) NULL,
			    `redirect_path` LONGTEXT NULL,
			    `redirect_product_id` BINARY(16) NULL,
			    `redirect_product_version_id` BINARY(16) NOT NULL,
			    `redirect_category_id` BINARY(16) NULL,
			    `redirect_category_version_id` BINARY(16) NOT NULL,
			    `has_deviating_redirect_sales_channel_domain` TINYINT(1) NULL DEFAULT '0',
			    `deviating_redirect_sales_channel_domain_id` BINARY(16) NULL,
			    `created_at` DATETIME(3) NOT NULL,
			    `updated_at` DATETIME(3) NULL,
			    PRIMARY KEY (`id`,`source_product_version_id`,`source_category_version_id`,`redirect_product_version_id`,`redirect_category_version_id`)
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
