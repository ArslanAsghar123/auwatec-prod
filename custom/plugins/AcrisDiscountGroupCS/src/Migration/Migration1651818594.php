<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1651818594 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1651818594;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'acris_discount_group', 'account_display')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group`
            ADD COLUMN `account_display` TINYINT(1) NULL DEFAULT '0'
        SQL;

            $connection->executeStatement($query);
        }

        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_discount_group_translation` (
                `display_text` LONGTEXT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                `acris_discount_group_id` BINARY(16) NOT NULL,
                `acris_discount_group_version_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                PRIMARY KEY (`acris_discount_group_id`,`acris_discount_group_version_id`,`language_id`),
                KEY `fk.acris_discount_group_translation.acris_discount_group_id` (`acris_discount_group_id`,`acris_discount_group_version_id`),
                KEY `fk.acris_discount_group_translation.language_id` (`language_id`),
                CONSTRAINT `fk.acris_discount_group_translation.acris_discount_group_id` FOREIGN KEY (`acris_discount_group_id`,`acris_discount_group_version_id`) REFERENCES `acris_discount_group` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.acris_discount_group_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
