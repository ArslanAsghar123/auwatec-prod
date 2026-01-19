<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1619421470FooterKit extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1591296689;
    }

    public function update(Connection $connection): void
    {
       $connection->exec('
            ALTER TABLE `cogi_footer_kit_translation`
            ADD COLUMN `navigation_block` JSON NULL AFTER `information_block`;
       ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
