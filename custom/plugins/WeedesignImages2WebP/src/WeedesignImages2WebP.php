<?php declare(strict_types=1);

namespace Weedesign\Images2WebP;

use Composer\Autoload\ClassLoader;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Weedesign\Images2WebP\Service\DeleteMediaFiles;
use Doctrine\DBAL\Connection;

class WeedesignImages2WebP extends Plugin
{

    public const PLUGIN_NAME = 'WeedesignImages2WebP';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $file = __DIR__.'/../vendor/autoload.php';

        if (!is_file($file)) {
            return;
        }

        $classLoader = require_once $file;

        if ($classLoader instanceof ClassLoader) {
            $classLoader->unregister();
            $classLoader->register(false);
        }
    }

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }
        
        $systemConfigService = $this->container->get(SystemConfigService::class);
        $deleteController = new DeleteMediaFiles($systemConfigService);

        $deleteMedia = $deleteController->delete();

        $connection = $this->container->get(Connection::class);

        $sql = "DELETE FROM `system_config` WHERE `configuration_key` LIKE '%WeedesignImages2WebP.config%'";
        $results = $connection->prepare($sql)->executeStatement();
        
    }

    public function update(UpdateContext $context): void
    {

        $systemConfigService = $this->container->get(SystemConfigService::class);
        $connection = $this->container->get(Connection::class);

        $sql = "DELETE FROM `system_config` WHERE `configuration_key` LIKE '%WeedesignImages2WebP.config.get%'";
        $results = $connection->prepare($sql)->executeStatement();

        $sql = "DELETE FROM `system_config` WHERE `configuration_key` = 'WeedesignImages2WebP.config.mediaFiles'";
        $results = $connection->prepare($sql)->executeStatement();

        $sql = "DELETE FROM `system_config` WHERE `configuration_key` = 'WeedesignImages2WebP.config.thumbnailSizes'";
        $results = $connection->prepare($sql)->executeStatement();

        $sql = "DELETE FROM `scheduled_task` WHERE `name` = 'images2webp_generate_media'";
        $results = $connection->prepare($sql)->executeStatement();

        $systemConfigService->set('WeedesignImages2WebP.config.upgrade',0);

        $systemConfigService->set('WeedesignImages2WebP.config.cronjob',0);

        $systemConfigService->set('WeedesignImages2WebP.config.cronjobInt',60);
        
        $systemConfigService->set('WeedesignImages2WebP.config.webpFinish', 0);

        if(empty($systemConfigService->get('WeedesignImages2WebP.config.email'))) {
            $systemConfigService->set('WeedesignImages2WebP.config.email','support@weedesign.de');
        }

    }

}
