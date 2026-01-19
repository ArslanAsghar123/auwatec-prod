<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup\Install;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swpa\SwpaBackup\Service\Config as ConfigService;

/**
 * Install default config
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Config
{
    use InheritanceUpdaterTrait;
    
    
    public function __construct(
        private readonly InstallContext      $context,
        private readonly Connection          $connection,
        private readonly SystemConfigService $configService,
        private readonly string              $rootDir
    )
    {
    }
    
    public function install(): void
    {
        $defaultBackupDirectory = $this->rootDir . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'swpa' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR;
        $defaultPublicDirectory = $this->rootDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
        
        $this->configService->set('SwpaBackup.settings.filesystemLocalRoot', $defaultBackupDirectory);
        $this->configService->set('SwpaBackup.settings.filesystemPublicRoot', $defaultPublicDirectory);
        $this->configService->set('SwpaBackup.settings.filesystemSftpTimeout', ConfigService::DEFAULT_SFTP_TIMEOUT);
        $this->configService->set('SwpaBackup.settings.filesystemFtpTimeout', ConfigService::DEFAULT_FTP_TIMEOUT);
        $this->configService->set('SwpaBackup.settings.filesystemSftpPort', ConfigService::DEFAULT_SFTP_PORT);
        $this->configService->set('SwpaBackup.settings.filesystemFtpPort', ConfigService::DEFAULT_FTP_PORT);
        $this->configService->set('SwpaBackup.settings.filesystemFtpPassive', ConfigService::DEFAULT_FTP_PASSIVE);
        $this->configService->set('SwpaBackup.settings.filesystemAwsVersion', ConfigService::DEFAULT_AWS_VERSION);
        
        $this->configService->set('SwpaBackup.settings.generalDestinationFilesystem', ConfigService::DEFAULT_DESTINATION_FILESYSTEM);
        $this->configService->set('SwpaBackup.settings.generalRunFrequency', ConfigService::DEFAULT_FREQUENCY);
        $this->configService->set('SwpaBackup.settings.generalMaintenanceMode', ConfigService::DEFAULT_MAINTENANCE_MODE);
        $this->configService->set('SwpaBackup.settings.generalMaintenanceManuel', ConfigService::DEFAULT_MAINTENANCE_MANUEL);
        $this->configService->set('SwpaBackup.settings.generalMaintenanceIp', ConfigService::DEFAULT_MAINTENANCE_IP);
        $this->configService->set('SwpaBackup.settings.generalBackupEnable', ConfigService::DEFAULT_BACKUP_ENABLED);
        $this->configService->set('SwpaBackup.settings.generalBackupType', ConfigService::TYPE_BACKUP_DATABASE);
        $this->configService->set('SwpaBackup.settings.generalRunTime', ConfigService::DEFAULT_STAT_TIME);
        $this->configService->set('SwpaBackup.settings.generalCleanBackup', ConfigService::DEFAULT_CLEAN_BACKUP);
        $this->configService->set('SwpaBackup.settings.generalCleanPeriod', ConfigService::DEFAULT_CLEAN_PERIOD);
        $this->configService->set('SwpaBackup.settings.generalExcludePath', ConfigService::DEFAULT_EXCLUDE_PATH);
        
        $this->configService->set('SwpaBackup.settings.backupInProgress', ConfigService::DEFAULT_BACKUP_IN_PROGRESS);
        
    }
    
}
