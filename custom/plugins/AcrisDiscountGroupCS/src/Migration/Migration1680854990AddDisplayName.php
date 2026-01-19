<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1680854990AddDisplayName extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1680854990;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'acris_discount_group_translation', 'display_name')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group_translation`
            ADD COLUMN `display_name` VARCHAR(255) NULL;
        SQL;

            $connection->executeStatement($query);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
