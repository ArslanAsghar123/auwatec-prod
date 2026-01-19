<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1677131835AddCalculationBaseOption extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1677131835;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'acris_discount_group', 'calculation_base')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group`
            ADD COLUMN `calculation_base` VARCHAR(50) DEFAULT 'price';
        SQL;

            $connection->executeStatement($query);
        }

        if (!$this->columnExists($connection, 'acris_discount_group', 'rrp_tax')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group`
            ADD COLUMN `rrp_tax` VARCHAR(50) DEFAULT 'auto';
        SQL;

            $connection->executeStatement($query);
        }

        if (!$this->columnExists($connection, 'acris_discount_group', 'rrp_tax_display')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group`
            ADD COLUMN `rrp_tax_display` VARCHAR(50) DEFAULT 'auto';
        SQL;

            $connection->executeStatement($query);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
