<?php declare(strict_types=1);

namespace Atl\SeoUrlManager;

use Atl\SeoUrlManager\Util\Lifecycle\Installer;
use Atl\SeoUrlManager\Util\Lifecycle\Uninstaller;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class AtlSeoUrlManager extends Plugin
{
    /**
     * @param InstallContext $installContext
     * @return void
     */
    public function install(InstallContext $installContext): void
    {
        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $installer = new Installer($customFieldSetRepository);
        $installer->install($installContext->getContext());
    }

    /**
     * @param UninstallContext $uninstallContext
     * @return void
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $uninstaller = new Uninstaller($customFieldSetRepository);
        $uninstaller->uninstall($uninstallContext->getContext());
    }

    /**
     * @param UpdateContext $updateContext
     * @return void
     */
    public function postUpdate(UpdateContext $updateContext): void
    {
        parent::postUpdate($updateContext);

        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $installer = new Installer($customFieldSetRepository);
        $installer->install($updateContext->getContext());
    }
}
