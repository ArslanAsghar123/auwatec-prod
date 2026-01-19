<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1654247015 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1654247015;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'acris_discount_group', 'min_quantity')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group`
            ADD COLUMN `min_quantity` INT(11) NULL DEFAULT 1;
        SQL;

            $connection->executeStatement($query);
        }

        if (!$this->columnExists($connection, 'acris_discount_group', 'max_quantity')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group`
            ADD COLUMN `max_quantity` INT(11) NULL;
        SQL;

            $connection->executeStatement($query);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
