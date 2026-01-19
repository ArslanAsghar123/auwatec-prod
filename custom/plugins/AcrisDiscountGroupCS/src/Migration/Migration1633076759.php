<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1633076759 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1633076759;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'acris_discount_group', 'rule_ids')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group` 
            ADD COLUMN `rule_ids` JSON NULL;
SQL;

            $connection->executeStatement($query);
        }

        if (!$this->columnExists($connection, 'acris_discount_group', 'product_stream_ids')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group` 
            ADD COLUMN `product_stream_ids` JSON NULL;
SQL;

            $connection->executeStatement($query);
        }

        if (!$this->checkExists($connection, 'json.acris_discount_group.rule_ids')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group` 
            ADD CONSTRAINT `json.acris_discount_group.rule_ids` CHECK (JSON_VALID(`rule_ids`));
SQL;

            $connection->executeStatement($query);
        }

        if (!$this->checkExists($connection, 'json.acris_discount_group.product_stream_ids')) {
            $query = <<<SQL
            ALTER TABLE `acris_discount_group` 
            ADD CONSTRAINT `json.acris_discount_group.product_stream_ids` CHECK (JSON_VALID(`product_stream_ids`));
SQL;

            $connection->executeStatement($query);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    protected function checkExists(Connection $connection, string $column): bool
    {
        $exists = $connection->fetchOne(
            'SELECT CONSTRAINT_NAME
FROM information_schema.CHECK_CONSTRAINTS
WHERE CONSTRAINT_NAME = :column',
            ['column' => $column]
        );

        return !empty($exists);
    }
}





