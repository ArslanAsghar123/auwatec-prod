<?php declare(strict_types=1);

namespace DreiscSeoPro;

use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Doctrine\DBAL\Connection;

class DreiscSeoPro extends Plugin
{
    /**
    * @param ContainerBuilder $container
    * @throws \Exception
    */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config/'));
        $loader->load('dependency.injection.xml');
    }

	public function uninstall(UninstallContext $context): void
	{
		parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

		/** Drop the database tables */
		$this->dropDatabase();

		/** Delete the custom fields */
        $this->deleteCustomFields();
	}

	private function dropDatabase(): void
	{
		$connection = $this->container->get(Connection::class);

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
		$connection->executeStatement('DROP TABLE IF EXISTS `dreisc_seo_redirect_import_export_log`');
		$connection->executeStatement('DROP TABLE IF EXISTS `dreisc_seo_redirect_import_export_file`');
		//$connection->executeStatement('DROP TABLE IF EXISTS `dreisc_seo_setting`');
		$connection->executeStatement('DROP TABLE IF EXISTS `dreisc_seo_bulk_template`');
		$connection->executeStatement('DROP TABLE IF EXISTS `dreisc_seo_bulk`');
		$connection->executeStatement('DROP TABLE IF EXISTS `dreisc_seo_redirect`');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
	}

    private function deleteCustomFields()
    {
        $connection = $this->container->get(Connection::class);

        $connection->executeStatement("
            DELETE FROM `custom_field` WHERE `name` LIKE 'dreisc_seo_%'
        ");

        $connection->executeStatement("
            DELETE FROM `custom_field_set` WHERE `name` LIKE 'dreisc_seo_%'
        ");
    }
}
