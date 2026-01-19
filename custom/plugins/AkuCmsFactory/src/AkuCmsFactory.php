<?php declare(strict_types=1);

namespace AkuCmsFactory;

use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin;
use Doctrine\DBAL\Connection;

class AkuCmsFactory extends Plugin {

    /**
     * @inheritDoc
     */
    public function uninstall(UninstallContext $context): void {
        if ($context->keepUserData()) {
            parent::uninstall($context);

            return;
        }

        // Remove all data
        $connection = $this->container->get(Connection::class);
        $connection->executeUpdate("DROP TABLE IF EXISTS `cms_factory_element`");
        $productRepository = $this->container->get('product.repository');
        $connection->executeUpdate("DELETE FROM cms_slot WHERE `type`='aku-cms-factory'");
        $connection->executeUpdate("DELETE FROM cms_block where `type`='aku-cms-factory'");

        parent::uninstall($context);

        return;
    }
}