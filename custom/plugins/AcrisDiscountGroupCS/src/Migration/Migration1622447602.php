<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1622447602 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1622447602;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_discount_group` (
                `id` BINARY(16) NOT NULL,
                `version_id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NULL,
                `product_version_id` BINARY(16) NULL,
                `customer_id` BINARY(16) NULL,
                `internal_name` VARCHAR(255) NULL,
                `active` TINYINT(1) NULL DEFAULT '1',
                `excluded` TINYINT(1) NULL DEFAULT '1',
                `active_from` DATETIME(3) NULL,
                `active_until` DATETIME(3) NULL,
                `priority` DOUBLE NULL DEFAULT 10,
                `discount` DOUBLE NOT NULL DEFAULT 0,
                `discount_type` VARCHAR(255) NULL DEFAULT 'percentage',
                `list_price_type` VARCHAR(255) NULL DEFAULT 'ignore',
                `customer_assignment_type` VARCHAR(255) NOT NULL DEFAULT 'rules',
                `product_assignment_type` VARCHAR(255) NOT NULL DEFAULT 'dynamicProductGroup',
                `calculation_type` VARCHAR(255) NULL DEFAULT 'discount',
                `material_group` VARCHAR(255) NULL,
                `discount_group` VARCHAR(255) NULL,
                `custom_fields` JSON NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`,`version_id`),
                CONSTRAINT `json.acris_discount_group.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
                KEY `fk.acris_discount_group.product_id` (`product_id`,`product_version_id`),
                KEY `fk.acris_discount_group.customer_id` (`customer_id`),
                CONSTRAINT `fk.acris_discount_group.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.acris_discount_group.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_discount_group_rule` (
                `discount_group_id` BINARY(16) NOT NULL,
                `acris_discount_group_version_id` BINARY(16) NOT NULL,
                `rule_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                PRIMARY KEY (`discount_group_id`,`rule_id`),
                KEY `fk.acris_discount_group_rule.discount_group_id` (`discount_group_id`,`acris_discount_group_version_id`),
                KEY `fk.acris_discount_group_rule.rule_id` (`rule_id`),
                CONSTRAINT `fk.acris_discount_group_rule.discount_group_id` FOREIGN KEY (`discount_group_id`,`acris_discount_group_version_id`) REFERENCES `acris_discount_group` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.acris_discount_group_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_discount_dynamic_groups` (
                `discount_group_id` BINARY(16) NOT NULL,
                `acris_discount_group_version_id` BINARY(16) NOT NULL,
                `product_stream_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                PRIMARY KEY (`discount_group_id`,`product_stream_id`),
                KEY `fk.acris_discount_dynamic_groups.discount_group_id` (`discount_group_id`,`acris_discount_group_version_id`),
                KEY `fk.acris_discount_dynamic_groups.product_stream_id` (`product_stream_id`),
                CONSTRAINT `fk.acris_discount_dynamic_groups.discount_group_id` FOREIGN KEY (`discount_group_id`,`acris_discount_group_version_id`) REFERENCES `acris_discount_group` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.acris_discount_dynamic_groups.product_stream_id` FOREIGN KEY (`product_stream_id`) REFERENCES `product_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        if (!$this->columnExists($connection, 'rule', 'acrisDiscountGroups')) {
            $this->updateInheritance($connection, 'rule', 'acrisDiscountGroups');
        }

        if (!$this->columnExists($connection, 'product_stream', 'acrisDiscountGroups')) {
            $this->updateInheritance($connection, 'product_stream', 'acrisDiscountGroups');
        }

        if (!$this->columnExists($connection, 'product', 'acrisDiscountGroups')) {
            $this->updateInheritance($connection, 'product', 'acrisDiscountGroups');
        }

        if (!$this->columnExists($connection, 'customer', 'acrisDiscountGroups')) {
            $this->updateInheritance($connection, 'customer', 'acrisDiscountGroups');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}





