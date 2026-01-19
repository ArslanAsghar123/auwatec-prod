<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1591296689FooterKit extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1591296689;
    }

    public function update(Connection $connection): void
    {
        $connection->exec('
        CREATE TABLE IF NOT EXISTS `cogi_footer_kit` (
            `id` BINARY(16) NOT NULL,
            `navigation_config` JSON NULL,
            `information_config` JSON NULL,
            `payment_shipping_config` JSON NULL,
            `bottom_config` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->exec('
        CREATE TABLE IF NOT EXISTS `cogi_footer_kit_translation` (
            `information_block` JSON NULL,
            `custom_link` JSON NULL,
            `social_media_string` VARCHAR(255) NULL,
            `payment_string` VARCHAR(255) NULL,
            `shipping_string` VARCHAR(255) NULL,
            `product_slider_title` VARCHAR(255) NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            `cogi_footer_kit_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`cogi_footer_kit_id`,`language_id`),
            KEY `fk.cogi_footer_kit_translation.cogi_footer_kit_id` (`cogi_footer_kit_id`),
            KEY `fk.cogi_footer_kit_translation.language_id` (`language_id`),
            CONSTRAINT `fk.cogi_footer_kit_translation.cogi_footer_kit_id` FOREIGN KEY (`cogi_footer_kit_id`) REFERENCES `cogi_footer_kit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.cogi_footer_kit_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->exec("
        INSERT INTO `cogi_footer_kit` (`id`, `navigation_config`, `information_config`, `payment_shipping_config`, `bottom_config`, `created_at`, `updated_at`)
        VALUES (UNHEX('DA6CD9776BC84463B25D5B6210DDB57B'),
        '{\"verticalSpacing\": \"\", \"horizontalSpacing\": \"\", \"backgroundColor\": \"\", \"fontColor\": \"\", \"active\": false, \"serviceHotline\": false, \"fontColorLink\": \"\", \"fontColorLinkHover\": \"\", \"transitionTime\": \"\", \"backgroundImage\": \"\", \"backgroundImageFit\": \"\", \"backgroundImageSize\": \"\", \"backgroundImageRepeat\": \"\", \"backgroundImagePosition\": \"\"}',
        '{\"basicSettings\": {\"active\": false, \"backgroundColor\": \"\", \"titleColor\": \"\", \"verticalSpacing\": \"\", \"horizontalSpacing\": \"\", \"center\": false, \"informationType\": \"custom\", \"numberOfNewProduct\": \"6\", \"salesChannelCustom\": \"\", \"salesChannelNew\": \"\", \"productType\": \"\"}, \"dynamicProductSettings\": {\"productIds\": []}}',
        '{\"paymentActive\": false, \"shippingActive\": false, \"verticalSpacing\": \"\", \"horizontalSpacing\": \"\", \"backgroundColor\": \"\", \"fontColor\": \"\"}',
        '{\"socialMedia\": [], \"socialMediaSettings\": {\"fontColor\": \"\", \"verticalSpacing\": \"\", \"horizontalSpacing\": \"\",\"iconSize\": \"\", \"active\": false, \"tab\": false},
        \"customLinkSettings\": {\"active\": false, \"tab\": false, \"fontColor\": \"\",\"fontColorHover\": \"\", \"transitionTime\": \"\", \"verticalSpacing\": \"\", \"horizontalSpacing\": \"\", \"fontSize\": \"\"},
        \"basicSettings\": {\"active\": false, \"backgroundColor\": \"\", \"vatAktive\": false, \"vatFontColor\": \"\", \"vatVerticalSpacing\": \"\", \"vatHorizontalSpacing\": \"\"},
        \"companyLogoSettings\": {\"active\": false, \"size\": \"\", \"media\": \"\", \"verticalSpacing\": \"\", \"horizontalSpacing\": \"\"}}',
        now(),
        NULL);
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
