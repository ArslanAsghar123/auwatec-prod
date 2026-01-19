<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1601476231AddDeletedEntity extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1601476231;
    }

    public function update(Connection $connection): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `deleted_entity` (
                `id` BINARY(16) NOT NULL,
                `entity_id` BINARY(16) NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `updated_at` DATETIME(3),
                `created_at` DATETIME(3) NOT NULL,
                PRIMARY KEY (`id`)
            );
        ";

        $connection->executeUpdate($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}