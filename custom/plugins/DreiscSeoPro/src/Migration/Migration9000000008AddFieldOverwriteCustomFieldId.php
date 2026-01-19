<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration9000000008AddFieldOverwriteCustomFieldId extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 9_000_000_008;
    }

    public function update(Connection $connection): void
    {
        try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_bulk`
                ADD `overwrite_custom_field_id` binary(16) NULL AFTER `overwrite`;
            ");
        } catch (\Exception) { }

        try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_bulk`
                ADD FOREIGN KEY (`overwrite_custom_field_id`) REFERENCES `custom_field` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ");
        } catch (\Exception) { }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
