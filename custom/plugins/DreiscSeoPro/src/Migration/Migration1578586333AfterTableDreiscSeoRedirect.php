<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Shopware\Core\Framework\Migration\MigrationStep;
use Doctrine\DBAL\Connection;

class Migration1578586333AfterTableDreiscSeoRedirect extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 1_578_586_333;
    }

    /**
     * @param Connection $connection
     */
    public function update(Connection $connection): void
	{
	    /** Try block to support the --keep-user-data uninstall */
	    try {
            $connection->executeStatement("
                ALTER TABLE `dreisc_seo_redirect`
                    ADD CONSTRAINT `json.source_sales_channel_domain3c304b43782f965a9346b02ec4289310` CHECK (JSON_VALID(`source_sales_channel_domain_restriction_ids`)),
                    ADD KEY `fk.dreisc_seo_redirect.source_sales_channel_domain_id` (`source_sales_channel_domain_id`),
                    ADD KEY `fk.dreisc_seo_redirect.source_product_id` (`source_product_id`,`source_product_version_id`),
                    ADD KEY `fk.dreisc_seo_redirect.source_category_id` (`source_category_id`,`source_category_version_id`),
                    ADD KEY `fk.dreisc_seo_redirect.redirect_sales_channel_domain_id` (`redirect_sales_channel_domain_id`),
                    ADD KEY `fk.deviating_redirect_sales_chaned92121b31c94809cf24df527cc3044f` (`deviating_redirect_sales_channel_domain_id`),
                    ADD KEY `fk.dreisc_seo_redirect.redirect_product_id` (`redirect_product_id`,`redirect_product_version_id`),
                    ADD KEY `fk.dreisc_seo_redirect.redirect_category_id` (`redirect_category_id`,`redirect_category_version_id`),
                    ADD CONSTRAINT `fk.dreisc_seo_redirect.source_sales_channel_domain_id` FOREIGN KEY (`source_sales_channel_domain_id`) REFERENCES `sales_channel_domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.dreisc_seo_redirect.source_product_id` FOREIGN KEY (`source_product_id`,`source_product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.dreisc_seo_redirect.source_category_id` FOREIGN KEY (`source_category_id`,`source_category_version_id`) REFERENCES `category` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.dreisc_seo_redirect.redirect_sales_channel_domain_id` FOREIGN KEY (`redirect_sales_channel_domain_id`) REFERENCES `sales_channel_domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.deviating_redirect_sales_chaned92121b31c94809cf24df527cc3044f` FOREIGN KEY (`deviating_redirect_sales_channel_domain_id`) REFERENCES `sales_channel_domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.dreisc_seo_redirect.redirect_product_id` FOREIGN KEY (`redirect_product_id`,`redirect_product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT `fk.dreisc_seo_redirect.redirect_category_id` FOREIGN KEY (`redirect_category_id`,`redirect_category_version_id`) REFERENCES `category` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE;
            ");
        } catch (\Exception) { }
	}

    /**
     * @param Connection $connection
     */
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
