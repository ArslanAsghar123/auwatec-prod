<?php

namespace Bfn\DirectDebit;

use Bfn\DirectDebit\Installer\CustomFieldInstaller;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class BfnDirectDebit extends Plugin
{
    public const PLUGIN_NAME = 'BfnDirectDebit';

    /**
     * @param InstallContext $context
     * @return void
     */
    public function install(InstallContext $context): void
    {
        /** @var EntityRepository $paymentRepository */
        $paymentRepository = $this->container->get('payment_method.repository');

        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        (new CustomFieldInstaller($this->container, $this->container->get('order.repository'), $this->container->get('customer.repository')))->install($context);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context): void
    {
        parent::update($context);

        (new CustomFieldInstaller($this->container, $this->container->get('order.repository'), $this->container->get('customer.repository')))->update($context);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context): void
    {
        parent::activate($context);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
        parent::deactivate($context);
    }

    /**
     * @param UninstallContext $context
     * @return void
     */
    public function uninstall(UninstallContext $context): void
    {
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        if (!$context->keepUserData()) {
            /** @var EntityRepository $paymentRepository */
            $paymentRepository = $this->container->get('payment_method.repository');

            /** @var EntityRepository $customFieldSetRepository */
            $customFieldSetRepository = $this->container->get('custom_field_set.repository');

            (new CustomFieldInstaller($this->container, $this->container->get('order.repository'), $this->container->get('customer.repository')))->uninstall($context);
        }
    }
}
