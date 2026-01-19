<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration9000000002InsertDefaultMetaTagLengthConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 9_000_000_002;
    }

    public function update(Connection $connection): void
    {
        /**
         * The inserts from this migration are no longer required
         */
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
