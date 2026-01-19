<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Databases;

use Exception;
use Ifsnop\Mysqldump\Mysqldump;
use Swpa\SwpaBackup\Service\Config;

/**
 * MySQL adapter
 *
 * @package   Swpa\SwpaBackup\Databases
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class MysqlDatabase implements DatabaseInterface
{
    
    /**
     * database type
     */
    const DATABASE_TYPE = 'mysql';
    
    public function __construct(private readonly Config $config)
    {
    }
    
    /**
     * @param string $type
     * @return bool
     */
    public function handles(string $type): bool
    {
        return strtolower($type) === static::DATABASE_TYPE;
    }
    
    /**
     * @param string $workingFile
     * @throws DatabaseConfigNotProvided
     * @throws DatabaseDumpNotCreated
     */
    public function dump(string $workingFile): void
    {
        try {
            $dump = new Mysqldump(
                $this->config->getDatabaseDSN(),
                $this->config->getDatabaseUser(),
                $this->config->getDatabasePassword(),
                [
                    'lock-tables' => $this->config->isLockTableEnabled(),
                    'add-drop-table' => $this->config->isDropTableEnabled(),
                    'add-drop-database' => $this->config->isDropDatabaseEnabled()
                ]
            );
        } catch (Exception $e) {
            throw new DatabaseConfigNotProvided('cannot configure database backup. ' . $e->getMessage());
        }
        
        try {
            $dump->start($workingFile);
        } catch (Exception $e) {
            throw new DatabaseDumpNotCreated('cannot create dump. ' . $e->getMessage());
        }
    }
}
