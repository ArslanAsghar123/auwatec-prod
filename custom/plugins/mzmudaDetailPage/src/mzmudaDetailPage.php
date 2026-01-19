<?php declare(strict_types=1);

namespace Mzmuda\mzmudaDetailPage;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class mzmudaDetailPage extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);


    }

    /**
     * @param InstallContext $installContext
     */
    public function install(InstallContext $installContext): void
    {


    }

    /**
     * @param UninstallContext $uninstallContext
     */
    public function uninstall(Plugin\Context\UninstallContext $uninstallContext): void
    {

        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

    }
}


