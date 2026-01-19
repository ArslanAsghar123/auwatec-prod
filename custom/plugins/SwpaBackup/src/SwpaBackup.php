<?php declare(strict_types=1);

namespace Swpa\SwpaBackup;

use Doctrine\DBAL\Connection;
use Exception;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swpa\SwpaBackup\DependencyInjection\Compiler\DatabasePass;
use Swpa\SwpaBackup\DependencyInjection\Compiler\FilesystemPass;
use Swpa\SwpaBackup\Setup;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Main class of the plugin SwpaBackup:
 *
 * @package Swpa\SwpaBackup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class SwpaBackup extends Plugin
{
    
    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function build(ContainerBuilder $container): void
    {
        $yamlLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
        $yamlLoader->load('services.yml');
        $container->addCompilerPass(new FilesystemPass());
        $container->addCompilerPass(new DatabasePass());
        parent::build($container);
    }
    
    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context): void
    {
        $install = new Setup\Install(
            $this->container->get(Connection::class),
            $this->container->get(SystemConfigService::class),
            $this->container->get('kernel')->getProjectDir()
        );
        $install->install($context);
        
        parent::install($context);
    }
    
    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context): void
    {
        $install = new Setup\Uninstall($this->container->get(Connection::class));
        $install->uninstall($context);
        
        parent::uninstall($context);
    }
    
    /**
     * @param ActivateContext $activateContext
     */
    public function activate(ActivateContext $activateContext): void
    {
        $install = new Setup\Activate($this->container->get(Connection::class));
        $install->activate($activateContext);
        
        parent::activate($activateContext);
    }
    
    /**
     * @param DeactivateContext $deactivateContext
     */
    public function deactivate(DeactivateContext $deactivateContext): void
    {
        $install = new Setup\Deactivate($this->container->get(Connection::class));
        $install->deactivate($deactivateContext);
        
        parent::deactivate($deactivateContext);
    }
}
