<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

#[Package('core')]
class Migration1733388745CreateGpsrTranslations extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1733388745;
    }

    public function update(Connection $connection): void
    {
        $this->renameTable($connection, 'acris_gprs_product_download', 'acris_gpsr_p_d');
        $this->renameTable($connection, 'acris_manufacturer_download', 'acris_mf_d');
        $this->renameTable($connection, 'acris_gpsr_manufacturer_download', 'acris_gpsr_mf_d');
        $this->renameTable($connection, 'acris_gpsr_note_download', 'acris_gpsr_n_d');
        $this->renameTable($connection, 'acris_gpsr_contact_download', 'acris_gpsr_c_d');

        $this->updateMediaDefaultFolder($connection, 'acris_gpsr_p_d', 'acris_gprs_product_download');
        $this->createTranslationTables($connection);

        if ($this->columnExists($connection, 'acris_gpsr_p_d', 'file_name')) {
            $this->fillTranslationTable($connection, 'acris_gpsr_p_d_translation', 'acris_gpsr_p_d_id', 'acris_gpsr_p_d');

            // TODO: In the new plugin version remove the old column
            /*$this->removeOldColumn($connection, 'acris_gpsr_p_d', 'file_name');
            $connection->executeStatement('
                ALTER TABLE `acris_gpsr_p_d`
                DROP COLUMN `file_name`;
            ');*/
        }

        if ($this->columnExists($connection, 'acris_mf_d', 'file_name')) {
            $this->fillTranslationTable($connection, 'acris_mf_d_translation', 'acris_mf_d_id', 'acris_mf_d');
        }

        if ($this->columnExists($connection, 'acris_gpsr_mf_d', 'file_name')) {
            $this->fillTranslationTable($connection, 'acris_gpsr_mf_d_translation', 'acris_gpsr_mf_d_id', 'acris_gpsr_mf_d');
        }

        if ($this->columnExists($connection, 'acris_gpsr_n_d', 'file_name')) {
            $this->fillTranslationTable($connection, 'acris_gpsr_n_d_translation', 'acris_gpsr_n_d_id', 'acris_gpsr_n_d');
        }

        if ($this->columnExists($connection, 'acris_gpsr_c_d', 'file_name')) {
            $this->fillTranslationTable($connection, 'acris_gpsr_c_d_translation', 'acris_gpsr_c_d_id', 'acris_gpsr_c_d');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function tableExists(Connection $connection, string $tableName): bool
    {
        return (bool) $connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table",
            ['table' => $tableName]
        );
    }

    private function renameTable(Connection $connection, string $old, string $new): void
    {
        if(!$this->tableExists($connection, $old) || $this->tableExists($connection, $new)) {
            return;
        }

        // rename table
        $sql = str_replace(
            ['#old#', '#new#'],
            [$old, $new],
            "RENAME TABLE `#old#` TO `#new#`;"
        );

        $connection->executeStatement($sql);
    }

    private function updateMediaDefaultFolder(Connection $connection, string $new, string $old): void
    {
        $oldExists = $connection->fetchOne(
            'SELECT `id` FROM `media_default_folder` WHERE `entity` = ?', [$old]
        );

        $newExists = $connection->fetchOne(
            'SELECT `id` FROM `media_default_folder` WHERE `entity` = ?', [$new]
        );

        if (!$oldExists || $newExists) {
            return;
        }

        $sql = str_replace(
            ['#new#', '#old#'],
            [$new, $old],
            "UPDATE media_default_folder SET entity = '#new#' WHERE entity = '#old#';"
        );

        $connection->executeStatement($sql);
    }

    private function fillTranslationTable(Connection $connection, string $translationTable, string $key, string $mainTable): void
    {
        try {
            // Transfer data to the translation table
            $sql = str_replace(
                ['#translationTable#', '#mainTable#', '#key#'],
                [$translationTable, $mainTable, $key],
                "INSERT INTO `#translationTable#` (
                        `file_name`, 
                        `custom_fields`, 
                        `created_at`, 
                        `updated_at`, 
                        `#key#`, 
                        `language_id`
                    )
                    SELECT 
                        `file_name`, 
                        NULL AS `custom_fields`, 
                        `created_at`, 
                        `updated_at`, 
                        `id` AS `#key#`, 
                        UNHEX(?) AS `language_id`
                    FROM `#mainTable#`
                    WHERE `file_name` IS NOT NULL;"
            );

            $connection->executeStatement($sql, [Defaults::LANGUAGE_SYSTEM]);
        } catch (\Throwable $e) {
            // do nothing
        }
    }

    private function createTranslationTables(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_p_d_translation` (
            `file_name` VARCHAR(255) NULL,
            `custom_fields` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            `acris_gpsr_p_d_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`acris_gpsr_p_d_id`,`language_id`),
            CONSTRAINT `json.acris_gpsr_p_d_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            KEY `fk.acris_gpsr_p_d_translation.acris_gpsr_p_d_id` (`acris_gpsr_p_d_id`),
            KEY `fk.acris_gpsr_p_d_translation.language_id` (`language_id`),
            CONSTRAINT `fk.acris_gpsr_p_d_translation.acris_gpsr_p_d_id` FOREIGN KEY (`acris_gpsr_p_d_id`) REFERENCES `acris_gpsr_p_d` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_p_d_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_mf_d_translation` (
            `file_name` VARCHAR(255) NULL,
            `custom_fields` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            `acris_mf_d_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`acris_mf_d_id`,`language_id`),
            CONSTRAINT `json.acris_mf_d_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            KEY `fk.acris_mf_d_translation.acris_mf_d_id` (`acris_mf_d_id`),
            KEY `fk.acris_mf_d_translation.language_id` (`language_id`),
            CONSTRAINT `fk.acris_mf_d_translation.acris_mf_d_id` FOREIGN KEY (`acris_mf_d_id`) REFERENCES `acris_mf_d` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_mf_d_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_mf_d_translation` (
            `file_name` VARCHAR(255) NULL,
            `custom_fields` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            `acris_gpsr_mf_d_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`acris_gpsr_mf_d_id`,`language_id`),
            CONSTRAINT `json.acris_gpsr_mf_d_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            KEY `fk.acris_gpsr_mf_d_translation.acris_gpsr_mf_d_id` (`acris_gpsr_mf_d_id`),
            KEY `fk.acris_gpsr_mf_d_translation.language_id` (`language_id`),
            CONSTRAINT `fk.acris_gpsr_mf_d_translation.acris_gpsr_mf_d_id` FOREIGN KEY (`acris_gpsr_mf_d_id`) REFERENCES `acris_gpsr_mf_d` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_mf_d_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_n_d_translation` (
            `file_name` VARCHAR(255) NULL,
            `custom_fields` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            `acris_gpsr_n_d_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`acris_gpsr_n_d_id`,`language_id`),
            CONSTRAINT `json.acris_gpsr_n_d_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            KEY `fk.acris_gpsr_n_d_translation.acris_gpsr_n_d_id` (`acris_gpsr_n_d_id`),
            KEY `fk.acris_gpsr_n_d_translation.language_id` (`language_id`),
            CONSTRAINT `fk.acris_gpsr_n_d_translation.acris_gpsr_n_d_id` FOREIGN KEY (`acris_gpsr_n_d_id`) REFERENCES `acris_gpsr_n_d` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_n_d_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `acris_gpsr_c_d_translation` (
            `file_name` VARCHAR(255) NULL,
            `custom_fields` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            `acris_gpsr_c_d_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`acris_gpsr_c_d_id`,`language_id`),
            CONSTRAINT `json.acris_gpsr_c_d_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            KEY `fk.acris_gpsr_c_d_translation.acris_gpsr_c_d_id` (`acris_gpsr_c_d_id`),
            KEY `fk.acris_gpsr_c_d_translation.language_id` (`language_id`),
            CONSTRAINT `fk.acris_gpsr_c_d_translation.acris_gpsr_c_d_id` FOREIGN KEY (`acris_gpsr_c_d_id`) REFERENCES `acris_gpsr_c_d` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.acris_gpsr_c_d_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }
}
