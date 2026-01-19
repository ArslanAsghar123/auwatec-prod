<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1725963058CreateManufacturerTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1725963058;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_mf` (
            `id` BINARY(16) NOT NULL,
            `active` TINYINT(1) NULL DEFAULT '0',
            `internal_id` VARCHAR(255) NULL,
            `priority` INT(11) NULL,
            `display_type` VARCHAR(255) NULL,
            `tab_position` VARCHAR(255) NULL,
            `description_display` VARCHAR(255) NULL,
            `description_position` VARCHAR(255) NULL,
            `display_separator` VARCHAR(255) NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
      CREATE TABLE IF NOT EXISTS `acris_gpsr_mf_translation` (
            `internal_name` VARCHAR(255) NULL,
            `internal_notice` VARCHAR(255) NULL,
            `headline` VARCHAR(255) NULL,
            `text` LONGTEXT NULL,
            `modal_info_text` LONGTEXT NULL,
            `modal_link_text` VARCHAR(255) NULL,
            `name` VARCHAR(255) NULL,
            `street` VARCHAR(255) NULL,
            `house_number` VARCHAR(255) NULL,
            `zipcode` VARCHAR(255) NULL,
            `city` VARCHAR(255) NULL,
            `country` VARCHAR(255) NULL,
            `phone_number` VARCHAR(255) NULL,
            `address` VARCHAR(255) NULL,
            `custom_fields` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            `acris_gpsr_mf_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`acris_gpsr_mf_id`,`language_id`),
            CONSTRAINT `json.acris_gpsr_mf_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            KEY `fk.acris_gpsr_mf_translation.acris_gpsr_mf_id` (`acris_gpsr_mf_id`),
            KEY `fk.acris_gpsr_mf_translation.language_id` (`language_id`),
            CONSTRAINT `fk.acris_gpsr_mf_translation.acris_gpsr_mf_id` FOREIGN KEY (`acris_gpsr_mf_id`) REFERENCES `acris_gpsr_mf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_mf_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_mf_rule` (
            `gpsr_manufacturer_id` BINARY(16) NOT NULL,
            `rule_id` BINARY(16) NOT NULL,
            `created_at` DATETIME(3) NOT NULL,
            PRIMARY KEY (`gpsr_manufacturer_id`,`rule_id`),
            KEY `fk.acris_gpsr_mf_rule.gpsr_manufacturer_id` (`gpsr_manufacturer_id`),
            KEY `fk.acris_gpsr_mf_rule.rule_id` (`rule_id`),
            CONSTRAINT `fk.acris_gpsr_mf_rule.gpsr_manufacturer_id` FOREIGN KEY (`gpsr_manufacturer_id`) REFERENCES `acris_gpsr_mf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_mf_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_mf_sales_channel` (
            `gpsr_manufacturer_id` BINARY(16) NOT NULL,
            `sales_channel_id` BINARY(16) NOT NULL,
            `created_at` DATETIME(3) NOT NULL,
            PRIMARY KEY (`gpsr_manufacturer_id`,`sales_channel_id`),
            KEY `fk.acris_gpsr_mf_sales_channel.gpsr_manufacturer_id` (`gpsr_manufacturer_id`),
            KEY `fk.acris_gpsr_mf_sales_channel.sales_channel_id` (`sales_channel_id`),
            CONSTRAINT `fk.acris_gpsr_mf_sales_channel.gpsr_manufacturer_id` FOREIGN KEY (`gpsr_manufacturer_id`) REFERENCES `acris_gpsr_mf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_mf_sales_channel.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_mf_stream` (
            `gpsr_manufacturer_id` BINARY(16) NOT NULL,
            `product_stream_id` BINARY(16) NOT NULL,
            `created_at` DATETIME(3) NOT NULL,
            PRIMARY KEY (`gpsr_manufacturer_id`,`product_stream_id`),
            KEY `fk.acris_gpsr_mf_stream.gpsr_manufacturer_id` (`gpsr_manufacturer_id`),
            KEY `fk.acris_gpsr_mf_stream.product_stream_id` (`product_stream_id`),
            CONSTRAINT `fk.acris_gpsr_mf_stream.gpsr_manufacturer_id` FOREIGN KEY (`gpsr_manufacturer_id`) REFERENCES `acris_gpsr_mf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_mf_stream.product_stream_id` FOREIGN KEY (`product_stream_id`) REFERENCES `product_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);

    }
}
