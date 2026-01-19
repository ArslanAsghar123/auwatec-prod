<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1731586145AddManufacturerVersionIdColumn extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1731586145;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `acris_manufacturer_download`
            ADD COLUMN `product_manufacturer_version_id` BINARY(16) NULL
        ');    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
