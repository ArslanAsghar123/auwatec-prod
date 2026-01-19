<?php declare(strict_types=1);

namespace phpSchmied\LastSeenProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1655472211CrossSelling extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1655472211;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate("
            CREATE TABLE IF NOT EXISTS `php_schmiede_cross_selling_customer_last_seen` (
                `product_id` BINARY(16) NOT NULL,
                `customer_id` BINARY(16) NOT NULL,
                `last_view` DATETIME NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`product_id`, `customer_id`),
                CONSTRAINT `fk.php_schmiede_cross_selling_customer_last_seen.product_id` FOREIGN KEY (`product_id`)
                    REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.php_schmiede_cross_selling_customer_last_seen.customer_id` FOREIGN KEY (`customer_id`)
                    REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}

