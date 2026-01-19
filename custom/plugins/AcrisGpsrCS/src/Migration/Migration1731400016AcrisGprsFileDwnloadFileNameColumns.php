<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1731400016AcrisGprsFileDwnloadFileNameColumns extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1731400016;
    }

    public function update(Connection $connection): void
    {
        $this->addColumnToTable($connection, 'acris_manufacturer_download', 'file_name');
        $this->addColumnToTable($connection, 'acris_gprs_product_download', 'file_name');
        $this->addColumnToTable($connection, 'acris_gpsr_manufacturer_download', 'file_name');
        $this->addColumnToTable($connection, 'acris_gpsr_note_download', 'file_name');
        $this->addColumnToTable($connection, 'acris_gpsr_contact_download', 'file_name');

    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    protected function addColumnToTable(Connection $connection, string $entity, string $propertyName): void
    {
        $sql = str_replace(
            ['#table#', '#column#'],
            [$entity, $propertyName],
            "ALTER TABLE `#table#` ADD COLUMN `#column#` VARCHAR(255) NULL"
        );

        $connection->executeUpdate($sql);
    }
}
