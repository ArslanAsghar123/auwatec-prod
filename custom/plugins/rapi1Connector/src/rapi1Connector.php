<?php

declare(strict_types=1);

namespace Rapidmail\Shopware;

use Doctrine\DBAL\Exception;
use Rapidmail\Shopware\Services\Rapi1User as Rapi1UserService;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class rapi1Connector extends Plugin
{
    private ?Rapi1UserService $rapi1UserService = null;

    /**
     * @throws Exception
     */
    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        $this->getRapi1UserService()->create();
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        parent::deactivate($deactivateContext);

        $this->getRapi1UserService()->delete();
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        parent::postUpdate($updateContext);

        $this->getRapi1UserService()->create();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        $this->getRapi1UserService()->delete();

        if (method_exists($uninstallContext, 'keepUserData') && $uninstallContext->keepUserData()) {
            return;
        }

        $this->getRapi1UserService()->delete();
        $this->getRapi1UserService()->getConnection()->executeUpdate('DROP TABLE IF EXISTS `deleted_entity`');
    }

    private function getRapi1UserService(): Rapi1UserService
    {
        if ($this->rapi1UserService === null) {
            $this->rapi1UserService = $this->container->get(Rapi1UserService::class);
        }

        return $this->rapi1UserService;
    }
}
