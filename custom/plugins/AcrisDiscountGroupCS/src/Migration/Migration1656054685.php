<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1656054685 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1656054685;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'acris_discount_group', 'internal_id')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group`
            ADD COLUMN `internal_id` VARCHAR(255) NULL AFTER `internal_name`
        SQL;

            $connection->executeStatement($query);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
