<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1645521082 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1645521082;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'acris_discount_group', 'list_price_type')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group`
            ALTER COLUMN `list_price_type` SET DEFAULT 'set'
SQL;

            $connection->executeStatement($query);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
