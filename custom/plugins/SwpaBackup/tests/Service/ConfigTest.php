<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Test\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigDefinition;
use Shopware\Core\System\SystemConfig\Util\ConfigReader;
use Swpa\SwpaBackup\Service\Config;
use Swpa\SwpaBackup\Service\ConfigException;
use Swpa\SwpaBackup\Test\Mock\DIContainerMock;
use Swpa\SwpaBackup\Test\Mock\Repositories\DefinitionInstanceRegistryMock;
use Swpa\SwpaBackup\Test\Mock\Settings\Service\SystemConfigServiceMock;

/**
 * Config Test
 *
 * @package   Swpa\SwpaBackup\Test
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class ConfigTest extends TestCase
{
    
    use KernelTestBehaviour;
    
    private const PREFIX = 'SwpaBackup.settings.';
    
    /**
     * test empty config
     */
    public function testEmptyConfig()
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock(),
            $this->getContainer()->get(Connection::class)
        );
        $this->expectException(ConfigException::class);
        $configService->isBackupEnabled();
    }
    
    /**
     * @return array[]
     */
    public function getProvider()
    {
        return [
            [static::PREFIX . '.generalBackupEnable', 'isBackupEnabled', true],
            [static::PREFIX . '.generalExcludePath', 'getExcludePaths', [
                '.idea',
                'platform',
                'files',
                'var/cache',
                'public/bundles',
                'public/sitemap',
                'public/theme',
                'public/thumbnail',
                'vendor',
                'dev-ops',
                '.git',
            ]],
            [static::PREFIX . '.generalDestinationFilesystem', 'getDestinationFilesystems', [Config::DEFAULT_DESTINATION_FILESYSTEM]],
            [static::PREFIX . '.generalCleanBackup', 'isClearBackupsEnabled', Config::DEFAULT_CLEAN_BACKUP],
            [static::PREFIX . '.generalCleanPeriod', 'getCleanPeriod', Config::DEFAULT_CLEAN_PERIOD],
            [static::PREFIX . '.generalRunTime', 'getRunTime', Config::DEFAULT_STAT_TIME],
            [static::PREFIX . '.generalRunFrequency', 'getRunFrequency', Config::DEFAULT_FREQUENCY],
        ];
    }
    
    /**
     * @dataProvider getProvider
     *
     * @param string $key
     * @param string $getterName
     * @param $value
     */
    public function testGet(string $key, string $getterName, $value): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        
        static::assertTrue(
            \method_exists($configService, $getterName),
            'getter ' . $getterName . ' does not exist'
        );
        static::assertSame($value, $configService->$getterName());
    }
    
    public function testGetDatabaseName(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        $connection = $this->getContainer()->get(Connection::class);
        
        static::assertSame($connection->getParams()['dbname'], $configService->getDatabaseName());
    }
    
    public function testGetDatabaseHost(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        $connection = $this->getContainer()->get(Connection::class);
        
        static::assertSame($connection->getParams()['host'], $configService->getDatabaseHost());
    }
    
    public function testGetDatabasePort(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        $connection = $this->getContainer()->get(Connection::class);
        
        static::assertSame($connection->getParams()['port'], $configService->getDatabasePort());
    }
    
    public function testGetDatabaseUser(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        $connection = $this->getContainer()->get(Connection::class);
        
        static::assertSame($connection->getParams()['user'], $configService->getDatabaseUser());
    }
    
    public function testGetDatabasePassword(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        $connection = $this->getContainer()->get(Connection::class);
        
        static::assertSame($connection->getParams()['password'], $configService->getDatabasePassword());
    }
    
    public function testIsLockTableEnabled(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        static::assertSame(true, $configService->isLockTableEnabled());
    }
    
    public function testIsDropTableEnabled(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        static::assertSame(true, $configService->isDropTableEnabled());
    }
    
    public function testIsDropDatabaseEnabled(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        static::assertSame(false, $configService->isDropDatabaseEnabled());
    }
    
    /**
     * @throws ConfigException
     */
    public function testGetDatabaseDSN(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        
        $dsn = "mysql:host={$configService->getDatabaseHost()};port={$configService->getDatabasePort()};dbname={$configService->getDatabaseName()}";
        
        static::assertSame($dsn, $configService->getDatabaseDSN());
    }
    
    /**
     * @throws ConfigException
     */
    public function testLocalFilesystemConfig(): void
    {
        $configService = new Config(
            $this->getKernel(),
            $this->createSystemConfigServiceMock($this->getRequiredConfigValues()),
            $this->getContainer()->get(Connection::class)
        );
        
        $config = $configService->getFilesystemConfig(Config::DEFAULT_DESTINATION_FILESYSTEM);
        $expected = ['root' => $this->getDefaultLocalFilesystemRootPath()];
        static::assertSame($expected, $config);
    }
    
    
    /**
     * @param array $settings
     * @return SystemConfigServiceMock
     */
    private function createSystemConfigServiceMock(array $settings = []): SystemConfigServiceMock
    {
        $definitionRegistry = new DefinitionInstanceRegistryMock([], new DIContainerMock());
        $systemConfigRepo = $definitionRegistry->getRepository(
            (new SystemConfigDefinition())->getEntityName()
        );
        
        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);
        $systemConfigService = new SystemConfigServiceMock($connection, $systemConfigRepo, new ConfigReader());
        foreach ($settings as $key => $value) {
            $systemConfigService->set($key, $value);
        }
        
        return $systemConfigService;
    }
    
    private function getDefaultLocalFilesystemRootPath(): string
    {
        
        return $this->getKernel()->getProjectDir() . '/files/test/backup/';
    }
    
    /**
     * @return array
     */
    protected function getRequiredConfigValues()
    {
        return [
            static::PREFIX . 'filesystemLocalRoot' => $this->getDefaultLocalFilesystemRootPath(),
            static::PREFIX . 'filesystemPublicRoot' => $this->getKernel()->getProjectDir() . '/public/',
            
            static::PREFIX . 'generalDestinationFilesystem' => Config::DEFAULT_DESTINATION_FILESYSTEM,
            static::PREFIX . 'generalRunFrequency' => Config::DEFAULT_FREQUENCY,
            static::PREFIX . 'generalMaintenanceMode' => Config::DEFAULT_MAINTENANCE_MODE,
            static::PREFIX . 'generalMaintenanceManuel' => Config::DEFAULT_MAINTENANCE_MANUEL,
            static::PREFIX . 'generalMaintenanceIp' => Config::DEFAULT_MAINTENANCE_IP,
            static::PREFIX . 'generalBackupEnable' => true,
            static::PREFIX . 'generalBackupType' => Config::TYPE_BACKUP_DATABASE,
            static::PREFIX . 'generalRunTime' => Config::DEFAULT_STAT_TIME,
            static::PREFIX . 'generalCleanBackup' => Config::DEFAULT_CLEAN_BACKUP,
            static::PREFIX . 'generalCleanPeriod' => Config::DEFAULT_CLEAN_PERIOD,
            static::PREFIX . 'generalExcludePath' => Config::DEFAULT_EXCLUDE_PATH,
            
            static::PREFIX . 'backupInProgress' => Config::DEFAULT_BACKUP_IN_PROGRESS,
        ];
    }
}
