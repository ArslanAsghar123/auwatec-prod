<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit;

use Shopware\Core\Framework\Plugin;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class CogiFooterKit extends Plugin
{
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);

        $connection->exec('DROP TABLE IF EXISTS `cogi_footer_kit_translation`');
        $connection->exec('DROP TABLE IF EXISTS `cogi_footer_kit`');
    }
}