<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Migration;

use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1620934144 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1620934144;
    }
    
    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        try {
            $connection->insert('system_config', [
                'id' => Uuid::randomBytes(),
                'configuration_key' => 'SwpaBackup.settings.filesystemFtpPassive',
                'configuration_value' => '{"_value": true}',
                'created_at' => (new DateTime())->format("Y-m-d H:i:s"),
                'updated_at' => (new DateTime())->format("Y-m-d H:i:s")
            ]);
        } catch (Exception $e) {
            throw new Exception("can't set default value for FTP passive mode. " . $e->getMessage());
        }
    }
    
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
