<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration9000000011AddRedirectColumnParameterForwarding extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 9_000_000_011;
    }

    public function update(Connection $connection): void
    {
        try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_redirect`
                ADD `parameter_forwarding` tinyint NULL DEFAULT '0' AFTER `redirect_http_status_code`;
            ");
        } catch (\Exception) { }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
