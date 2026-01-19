<?php declare(strict_types=1);

namespace Intedia\Doofinder;

use Doctrine\DBAL\Connection;
use Intedia\Doofinder\Core\Content\Settings\Service\CommunicationHandler;
use Intedia\Doofinder\Core\Content\Settings\Service\SettingsHandler;
use Intedia\Doofinder\Core\Content\Update\Service\UpdateHandler;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class IntediaDoofinderSW6
 * @package Intedia\Doofinder
 */
class IntediaDoofinderSW6 extends Plugin
{
    /**
     * @param ActivateContext $activateContext
     * @return void
     */
    public function activate(ActivateContext $activateContext): void
    {
    }

    /**
     * @param InstallContext $installContext
     * @return void
     */
    public function install(InstallContext $installContext): void
    {
        $this->addDoofinderBaseTable();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $locator = new FileLocator('Resources/config');

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
        ]);

        $configLoader = new DelegatingLoader($resolver);

        $confDir = \rtrim($this->getPath(), '/') . '/Resources/config';

        $configLoader->load($confDir . '/{packages}/*.yaml', 'glob');
    }


    /**
     * @param UpdateContext $updateContext
     * @return void
     * @throws \Exception
     */
    public function update(UpdateContext $updateContext): void
    {
        if($updateContext->getPlugin()->isActive() === true) {
            $updateHandler = new UpdateHandler(
                $this->container->get('product_export.repository'),
                $this->container->get(SettingsHandler::class),
                $this->container->get('Shopware\Core\System\SystemConfig\SystemConfigService'),
                $this->container->get('translator'),
                $this->container->get(CommunicationHandler::class)
            );

            switch ($updateContext->getCurrentPluginVersion()) {
                case '1.0.0':
                    $updateHandler->updateDoofinderExport101();
                case '1.0.1':
                case '1.0.2':
                    // Nothing
                case '1.0.3':
                    // Nothing
                case '1.0.4':
                    $updateHandler->updateDoofinderExport105();
                case '1.0.5':
                case '1.0.6':
                case '1.0.7':
                    $updateHandler->updateDoofinderExport108();
                case '1.0.8':
                    $updateHandler->updateDoofinderExport109();
                case '1.0.9':
                case '1.0.10':
                case '1.0.11':
                    $updateHandler->updateDoofinderExport110();
                case '1.1.0':
                case '1.1.1':
                case '1.1.2':
                case '2.0.0':
                    $this->addDoofinderBaseTable();
                    $updateHandler->updateDoofinderTo200();
                case '2.2.0':
                    $updateHandler->updateDoofinderExport221();
                case '2.2.1':
                    // Nothing
                case '2.2.2':
                    // Nothing
                case '2.2.3':
                    $updateHandler->updateDoofinderExport224();
                case '2.2.4':
                    // Nothing
                case '2.2.5':
                    // Nothing
                case '2.2.6':
                    // Nothing
                case '2.2.7':
                    // Nothing
                case '2.2.8':
                    // Nothing
                case '2.2.9':
                    // Nothing
                case '2.3.0':
                    // Nothing
                case '2.3.1':
                    // Nothing
            }
        } else {
            throw new \Exception('Please activate the Plugin');
        }
    }

    /**
     * @param UninstallContext $uninstallContext
     * @return void
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if (!$uninstallContext->keepUserData()) {
            $updateHandler = new UpdateHandler(
                $this->container->get( 'product_export.repository'),
                new SettingsHandler(
                    $this->container->get('product_export.repository'),
                    $this->container->get('product_stream.repository'),
                    $this->container->get('sales_channel.repository'),
                    $this->container->get('sales_channel_domain.repository'),
                    $this->container->get('language.repository'),
                    $this->container->get('currency.repository')
                ),
                $this->container->get('Shopware\Core\System\SystemConfig\SystemConfigService'),
                $this->container->get('translator')
            );

            $connection = $this->container->get(Connection::class);
            $connection->executeUpdate('DROP TABLE IF EXISTS `intedia_doofinder_layer`');

            $updateHandler->deleteDooFinderExports();
            $updateHandler->deleteDooFinderStream();
        }
    }

    private function addDoofinderBaseTable()
    {
        $connection = $this->container->get(Connection::class);
        $connection->executeUpdate('
                        CREATE TABLE IF NOT EXISTS `intedia_doofinder_layer` (
                          `id` binary(16) NOT NULL,
                          `doofinder_channel_id` binary(16) DEFAULT NULL,
                          `doofinder_hash_id` varchar(32) COLLATE utf8mb4_unicode_ci,
                          `doofinder_store_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `domain_id` binary(16) DEFAULT NULL,
                          `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `trigger` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                          `status` varchar(255) DEFAULT NULL,
                          `status_message` text collate utf8mb4_unicode_ci,
                          `status_date` datetime DEFAULT NULL,
                          `status_received_date` datetime DEFAULT NULL,
                          `created_at` datetime DEFAULT NULL,
                          `updated_at` datetime DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }
}
