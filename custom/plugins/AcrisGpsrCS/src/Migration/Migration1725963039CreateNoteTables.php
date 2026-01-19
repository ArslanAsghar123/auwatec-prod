<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1725963039CreateNoteTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1725963039;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_note` (
                `id` BINARY(16) NOT NULL,
                `active` TINYINT(1) NULL DEFAULT '0',
                `internal_id` VARCHAR(255) NULL,
                `note_type` VARCHAR(255) NULL,
                `priority` INT(11) NULL DEFAULT '10',
                `display_type` VARCHAR(255) NULL,
                `tab_position` VARCHAR(255) NULL,
                `description_display` VARCHAR(255) NULL,
                `description_position` VARCHAR(255) NULL,
                `display_separator` VARCHAR(255) NULL,
                `background_color` VARCHAR(255) NULL,
                `border_color` VARCHAR(255) NULL,
                `headline_color` VARCHAR(255) NULL,
                `hint_headline_seo_size` VARCHAR(255) NULL,
                `hint_alignment` VARCHAR(255) NULL,
                `hint_headline_color` VARCHAR(255) NULL,
                `hint_enable_headline_size` TINYINT(1) NULL DEFAULT '0',
                `hint_headline_size` VARCHAR(255) NULL,
                `media_position` VARCHAR(255) NULL,
                `media_size` INT(11) NULL,
                `mobile_visibility` VARCHAR(255) NULL,
                `media_id` BINARY(16) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            KEY `fk.acris_gpsr_note.media_id` (`media_id`),
            CONSTRAINT `fk.acris_gpsr_note.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_note_translation` (
                `internal_name` VARCHAR(255) NULL,
                `internal_notice` VARCHAR(255) NULL,
                `headline` VARCHAR(255) NULL,
                `text` LONGTEXT NULL,
                `modal_info_text` LONGTEXT NULL,
                `modal_link_text` VARCHAR(255) NULL,
                `custom_fields` JSON NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                `acris_gpsr_note_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`acris_gpsr_note_id`,`language_id`),
            CONSTRAINT `json.acris_gpsr_note_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            KEY `fk.acris_gpsr_note_translation.acris_gpsr_note_id` (`acris_gpsr_note_id`),
            KEY `fk.acris_gpsr_note_translation.language_id` (`language_id`),
            CONSTRAINT `fk.acris_gpsr_note_translation.acris_gpsr_note_id` FOREIGN KEY (`acris_gpsr_note_id`) REFERENCES `acris_gpsr_note` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_note_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_note_rule` (
                `gpsr_note_id` BINARY(16) NOT NULL,
                `rule_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
            PRIMARY KEY (`gpsr_note_id`,`rule_id`),
            KEY `fk.acris_gpsr_note_rule.gpsr_note_id` (`gpsr_note_id`),
            KEY `fk.acris_gpsr_note_rule.rule_id` (`rule_id`),
            CONSTRAINT `fk.acris_gpsr_note_rule.gpsr_note_id` FOREIGN KEY (`gpsr_note_id`) REFERENCES `acris_gpsr_note` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_note_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_note_sales_channel` (
                    `gpsr_note_id` BINARY(16) NOT NULL,
                    `sales_channel_id` BINARY(16) NOT NULL,
                    `created_at` DATETIME(3) NOT NULL,
                PRIMARY KEY (`gpsr_note_id`,`sales_channel_id`),
                KEY `fk.acris_gpsr_note_sales_channel.gpsr_note_id` (`gpsr_note_id`),
                KEY `fk.acris_gpsr_note_sales_channel.sales_channel_id` (`sales_channel_id`),
                CONSTRAINT `fk.acris_gpsr_note_sales_channel.gpsr_note_id` FOREIGN KEY (`gpsr_note_id`) REFERENCES `acris_gpsr_note` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.acris_gpsr_note_sales_channel.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_note_stream` (
                    `gpsr_note_id` BINARY(16) NOT NULL,
                    `product_stream_id` BINARY(16) NOT NULL,
                    `created_at` DATETIME(3) NOT NULL,
                PRIMARY KEY (`gpsr_note_id`,`product_stream_id`),
                KEY `fk.acris_gpsr_note_stream.gpsr_note_id` (`gpsr_note_id`),
                KEY `fk.acris_gpsr_note_stream.product_stream_id` (`product_stream_id`),
                CONSTRAINT `fk.acris_gpsr_note_stream.gpsr_note_id` FOREIGN KEY (`gpsr_note_id`) REFERENCES `acris_gpsr_note` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.acris_gpsr_note_stream.product_stream_id` FOREIGN KEY (`product_stream_id`) REFERENCES `product_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);
    }
}
