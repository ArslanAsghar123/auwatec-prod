<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration9000000009AddFieldAiPrompt extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 9_000_000_009;
    }

    public function update(Connection $connection): void
    {
        try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_bulk_template`
                ADD `ai_prompt` tinyint(1) NULL DEFAULT 0 AFTER `spaceless`;
            ");
        } catch (\Exception) { }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
