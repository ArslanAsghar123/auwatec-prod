<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swpa\SwpaBackup\Setup\Install\Config;
use Swpa\SwpaBackup\Setup\Install\Schema;

/**
 * Install
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Install
{
    public function __construct(
        private readonly Connection          $connection,
        private readonly SystemConfigService $configService,
        private readonly string              $projectRootDir
    )
    {
    }
    
    public function install(InstallContext $context): void
    {
        $schema = new Schema($context, $this->connection);
        $schema->install();
        $config = new Config($context, $this->connection, $this->configService, $this->projectRootDir);
        $config->install();
    }
}
