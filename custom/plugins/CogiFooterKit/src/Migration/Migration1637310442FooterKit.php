<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1637310442FooterKit extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1637310442;
    }

    public function update(Connection $connection): void
    {
        $connection->exec('
            ALTER TABLE `cogi_footer_kit`
            ADD COLUMN `sales_channel_id` BINARY(16) NULL AFTER `id`,
            ADD COLUMN `name` VARCHAR(255) NULL AFTER `sales_channel_id`;
       ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
