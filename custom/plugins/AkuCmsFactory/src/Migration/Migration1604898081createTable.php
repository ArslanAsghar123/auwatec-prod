<?php declare(strict_types=1);

namespace AkuCmsFactory\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1604898081createTable extends MigrationStep {
    public function getCreationTimestamp(): int {
        return 1604898081;
    }

    public function update(Connection $connection): void {
        // implement update

        $query = "
            CREATE TABLE IF NOT EXISTS `cms_factory_element` (
                `id` BINARY(16) NOT NULL,
                `name`   VARCHAR(255)    NOT NULL,
                `fields`   LONGTEXT    NULL,
                `twig`   LONGTEXT    NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NULL,

                PRIMARY KEY (id)
            )
                ENGINE = InnoDB
                DEFAULT CHARSET = utf8mb4
                COLLATE = utf8mb4_unicode_ci;
        ";
        $connection->executeUpdate($query);
    }

    public function updateDestructive(Connection $connection): void {
        // nothing to do
    }
}
