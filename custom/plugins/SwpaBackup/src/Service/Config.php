<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Service;

use Doctrine\DBAL\Connection;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Service to work with configuration
 *
 * @package   Swpa\SwpaBackup\Service
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class Config
{
    const DEFAULT_BACKUP_DIR_NAME = 'swpa/backup';
    const
        DEFAULT_STAT_TIME = ['0:00'],
        DEFAULT_BACKUP_ENABLED = false,
        DEFAULT_BACKUP_IN_PROGRESS = 0,
        DEFAULT_FREQUENCY = 'daily',
        DEFAULT_DESTINATION_FILESYSTEM = 'local',
        DEFAULT_MAINTENANCE_MODE = false,
        DEFAULT_MAINTENANCE_MANUEL = false,
        DEFAULT_MAINTENANCE_IP = '',
        DEFAULT_SFTP_TIMEOUT = 60,
        DEFAULT_SFTP_PORT = 22,
        DEFAULT_FTP_PORT = 21,
        DEFAULT_FTP_TIMEOUT = 60,
        DEFAULT_AWS_VERSION = 'latest',
        DEFAULT_FTP_PASSIVE = true,
        DEFAULT_CLEAN_BACKUP = false,
        DEFAULT_CLEAN_PERIOD = 1,
        DEFAULT_EXCLUDE_PATH = "vendor\ndev-ops\n.git";
    
    const TYPE_BACKUP_DATABASE = 0,
        TYPE_BACKUP_DATABASE_MEDIA = 1,
        TYPE_BACKUP_SYSTEM = 2,
        TYPE_BACKUP_SYSTEM_WITHOUT_MEDIA = 3;
    
    private static array $excludePaths = [
        '.idea',
        'platform',
        'files',
        'var/cache',
        'public/bundles',
        'public/sitemap',
        'public/theme',
        'public/thumbnail'
    ];
    private array $config = [];
    
    public function __construct(
        private readonly KernelInterface     $kernel,
        private readonly SystemConfigService $systemConfigService,
        private readonly Connection          $connection,
        private readonly LoggerInterface     $logger
    )
    {
    }
    
    public function ping(): void
    {
        $this->logger->info('check mysql connection..');
        if (!$this->connection->isConnected()) {
            $this->logger->warning('mysql has gone away. try reconnect');
            try {
                $this->connection->close();
                $this->connection->connect();
            } catch (Exception $e) {
                $this->logger->critical('cannot reconnect to mysql. ' . $e->getMessage());
            }
        }
    }
    
    public function isBackupEnabled(): bool
    {
        try {
            $status = $this->getConfigValue('general', 'backup', 'enable');
        } catch (ConfigException $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
        return $status;
    }
    
    /**
     * @throws ConfigException
     */
    private function getConfigValue($namespace, $name = null, $field = null)
    {
        if (empty($this->config)) {
            $config = $this->systemConfigService->getDomain('SwpaBackup');
            foreach ($config as $key => $value) {
                list($domain, $prefix, $pathName) = explode('.', $key);
                $pieces = preg_split('/(?=[A-Z])/', $pathName);
                $this->config[strtolower($pieces[0])][strtolower($pieces[1])][strtolower($pieces[2])] = $value;
            }
        }
        if (array_key_exists($namespace, $this->config)) {
            if ($name === null) {
                return $this->config[$namespace];
            }
            if (!array_key_exists($name, $this->config[$namespace])) {
                return null;
            }
            if ($field === null) {
                return $this->config[$namespace][$name];
            }
            if (!array_key_exists($field, $this->config[$namespace][$name])) {
                return null;
            }
            return $this->config[$namespace][$name][$field];
        }
        throw new ConfigException("the config [{$namespace}.{$name}.{$field}] is not defined");
    }
    
    /**
     * @return array
     * @throws ConfigException
     */
    public function getExcludePaths(): array
    {
        if (!$paths = $this->getConfigValue('general', 'exclude', 'path')) {
            return static::$excludePaths;
        }
        
        $paths = explode("\n", trim($paths));
        if (!is_array($paths)) {
            return static::$excludePaths;
        }
        return array_merge(static::$excludePaths, $paths);
    }
    
    /**
     * get database user
     * @return string
     * @throws ConfigException
     */
    public function getDatabaseUser(): string
    {
        $params = $this->connection->getParams();
        if (is_array($params) && array_key_exists('user', $params)) {
            return $params['user'];
        }
        throw new ConfigException("can't retrieve database user");
    }
    
    /**
     * get database password
     * @return string
     * @throws ConfigException
     */
    public function getDatabasePassword(): string
    {
        $params = $this->connection->getParams();
        if (is_array($params) && array_key_exists('password', $params)) {
            return $params['password'];
        }
        throw new ConfigException("can't retrieve database password");
    }
    
    /**
     * @return array
     * @throws ConfigException
     */
    public function getRunTime(): array
    {
        if (!$time = $this->getConfigValue('general', 'run', 'time')) {
            return static::DEFAULT_STAT_TIME;
        }
        return $time;
    }
    
    /**
     * @return string
     * @throws ConfigException
     */
    public function getRunFrequency(): string
    {
        if (!$frequency = $this->getConfigValue('general', 'run', 'frequency')) {
            return static::DEFAULT_FREQUENCY;
        }
        return $frequency;
    }
    
    /**
     * databases: is lock table enabled
     * @return bool
     */
    public function isLockTableEnabled(): bool
    {
        return true;
    }
    
    /**
     * databases: is drop table option enabled
     * @return bool
     */
    public function isDropTableEnabled(): bool
    {
        return true;
    }
    
    /**
     * databases: is drop database option enabled
     * @return bool
     */
    public function isDropDatabaseEnabled(): bool
    {
        return false;
    }
    
    /**
     * databases: generate DSN string
     * @return string
     * @throws ConfigException
     */
    public function getDatabaseDSN(): string
    {
        return "mysql:host={$this->getDatabaseHost()};port={$this->getDatabasePort()};dbname={$this->getDatabaseName()}";
    }
    
    /**
     * @throws ConfigException
     */
    public function getDatabaseHost(): string
    {
        $params = $this->connection->getParams();
        if (is_array($params) && array_key_exists('host', $params)) {
            return $params['host'];
        }
        throw new ConfigException("can't retrieve database host");
    }
    
    /**
     * get database port
     * @return int
     * @throws ConfigException
     */
    public function getDatabasePort(): int
    {
        $params = $this->connection->getParams();
        if (is_array($params) && array_key_exists('port', $params)) {
            return intval($params['port']);
        }
        throw new ConfigException("can't retrieve database port");
    }
    
    /**
     * get database name
     * @return string
     * @throws ConfigException
     */
    public function getDatabaseName(): string
    {
        $params = $this->connection->getParams();
        if (is_array($params) && array_key_exists('dbname', $params)) {
            return $params['dbname'];
        }
        throw new ConfigException("can't retrieve database dbname");
    }
    
    /**
     * Get filesystem config
     *
     * @param $name
     * @param null $field
     * @return mixed
     * @throws ConfigException
     */
    public function getFilesystemConfig($name, $field = null): array|string
    {
        $fileSystemConfig = $this->getConfigValue('filesystem', $name, $field);
        if (is_null($fileSystemConfig)) {
            throw new ConfigException("cannot find config for filesystem [$name]");
        }
        return $fileSystemConfig;
    }
    
    /**
     * get default backup type
     * @return int
     */
    public function getBackupType(): int
    {
        try {
            return (int)$this->getConfigValue('general', 'backup', 'type');
        } catch (ConfigException $e) {
            $this->logger->critical($e->getMessage());
            return static::TYPE_BACKUP_DATABASE;
        }
    }
    
    /**
     * some directory of the project
     * @param string $dir
     * @return string
     */
    public function getProjectDirectory(string $dir): string
    {
        return $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . trim($dir, DIRECTORY_SEPARATOR);
    }
    
    /**
     * get default backup directory
     * @return string
     */
    public function getDefaultBackupDirectory(): string
    {
        try {
            $defaultBackupDir = $this->getConfigValue('filesystem', 'local', 'root');
            if (!$defaultBackupDir) {
                return $this->getProjectVarDirectory() . DIRECTORY_SEPARATOR . self::DEFAULT_BACKUP_DIR_NAME;
            }
        } catch (ConfigException $e) {
            $this->logger->critical($e->getMessage());
            return $this->getProjectVarDirectory() . DIRECTORY_SEPARATOR . self::DEFAULT_BACKUP_DIR_NAME;
        }
        return $defaultBackupDir;
    }
    
    /**
     * get project view directory
     * @return string
     */
    public function getProjectVarDirectory(): string
    {
        return $this->getProjectRootDirectory() . DIRECTORY_SEPARATOR . 'var';
    }
    
    /**
     * root directory of the project
     * @return string
     */
    public function getProjectRootDirectory(): string
    {
        return $this->kernel->getProjectDir();
    }
    
    /**
     * get project cache directory
     * @return string
     */
    public function getProjectCacheDirectory(): string
    {
        return $this->getProjectVarDirectory() . DIRECTORY_SEPARATOR . 'cache';
    }
    
    /**
     * get destination filesystems
     * @return array
     */
    public function getDestinationFilesystems(): array
    {
        try {
            $destinationFilesystemCode = $this->getConfigValue('general', 'destination', 'filesystem');
            if (!$destinationFilesystemCode) {
                return [static::DEFAULT_DESTINATION_FILESYSTEM];
            }
        } catch (ConfigException $e) {
            $this->logger->critical($e->getMessage());
            return [static::DEFAULT_DESTINATION_FILESYSTEM];
        }
        
        return [$destinationFilesystemCode];
    }
    
    /**
     * @return bool
     */
    public function isClearBackupsEnabled(): bool
    {
        try {
            return $this->getConfigValue('general', 'clean', 'backup') === true;
        } catch (ConfigException $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }
    
    public function isMaintenanceModeEnabled(): bool
    {
        try {
            return $this->getConfigValue('general', 'maintenance', 'mode') === true;
        } catch (ConfigException $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }
    
    /**
     * @return int
     */
    public function getCleanPeriod(): int
    {
        
        try {
            if (!$period = $this->getConfigValue('general', 'clean', 'period')) {
                return static::DEFAULT_CLEAN_PERIOD;
            }
        } catch (ConfigException $e) {
            $this->logger->critical($e->getMessage());
            return static::DEFAULT_CLEAN_PERIOD;
        }
        return intval($period);
    }
    
    public function enableMaintenanceMode(): void
    {
        if (!$this->isMaintenanceModeEnabled()) {
            return;
        }
        $this->createSnapshotOfMaintenanceMode();
        $ips = $this->fetchMaintenanceIps();
        $builder = $this->connection->createQueryBuilder();
        foreach ($this->fetchAllSalesChannels() as $channel) {
            $builder
                ->update('sales_channel')
                ->set('maintenance', 1)
                ->set('maintenance_ip_whitelist', ":ips")
                ->where('id = :id')
                ->setParameter('id', $channel['id'])
                ->setParameter('ips', json_encode($ips));
            try {
                $builder->executeStatement();
            } catch (\Doctrine\DBAL\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
    
    public function disableMaintenanceMode(): void
    {
        if (!$this->isMaintenanceModeEnabled()) {
            return;
        }
        $snapshot = $this->fetchMaintenanceModeSnapshot();
        $builder = $this->connection->createQueryBuilder();
        foreach ($this->fetchAllSalesChannels() as $channel) {
            $row = $snapshot[Uuid::fromBytesToHex($channel['id'])] ?? ['maintenance' => 0, 'maintenance_ip_whitelist' => $channel['maintenance_ip_whitelist']];
            $builder
                ->update('sales_channel')
                ->set('maintenance', $row['maintenance'])
                ->set('maintenance_ip_whitelist', ":ips")
                ->where('id = :id')
                ->setParameter('id', $channel['id'])
                ->setParameter('ips', $row['maintenance_ip_whitelist']);
            try {
                $builder->executeStatement();
            } catch (\Doctrine\DBAL\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
    
    private function fetchMaintenanceModeSnapshot(): array
    {
        $snapshot = $this->systemConfigService->get('SwpaBackup.settings.maintenanceModeSnapshot');
        return $snapshot ? json_decode($snapshot, true) : [];
    }
    
    private function createSnapshotOfMaintenanceMode(): void
    {
        $snapshot = [];
        foreach ($this->fetchAllSalesChannels() as $channel) {
            $snapshot[Uuid::fromBytesToHex($channel['id'])] = [
                'maintenance' => $channel['maintenance'],
                'maintenance_ip_whitelist' => $channel['maintenance_ip_whitelist']
            ];
        }
        $this->systemConfigService->set('SwpaBackup.settings.maintenanceModeSnapshot', json_encode($snapshot));
    }
    
    private function fetchAllSalesChannels(): array
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select("*")
            ->from('sales_channel');
        try {
            $result = $builder->executeQuery()->fetchAllAssociative();
        } catch (\Doctrine\DBAL\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = [];
        }
        return $result;
    }
    
    private function fetchMaintenanceIps(): array
    {
        try {
            $ips = $this->getConfigValue('general', 'maintenance', 'ip');
        } catch (ConfigException $e) {
            $this->logger->critical($e->getMessage());
            return [];
        }
        return explode(",", $ips);
    }
}
