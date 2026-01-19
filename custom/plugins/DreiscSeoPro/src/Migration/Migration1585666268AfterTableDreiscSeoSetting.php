<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1585666268AfterTableDreiscSeoSetting extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 1_585_666_268;
    }

    /**
     * @param Connection $connection
     */
    public function update(Connection $connection): void
	{
        // implement update
	}

    /**
     * @param Connection $connection
     */
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
