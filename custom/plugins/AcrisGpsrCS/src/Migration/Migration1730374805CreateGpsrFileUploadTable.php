<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1730374805CreateGpsrFileUploadTable extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1730374805;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_gprs_product_download` (
    `id` BINARY(16) NOT NULL,
    `media_id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `gpsr_type` VARCHAR(255) NULL,
    `position` INT(11) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `preview_image_enabled` TINYINT(1) NULL DEFAULT '0',
    `preview_media_id` BINARY(16) NULL,
    PRIMARY KEY (`id`),
    KEY `fk.acris_gprs_product_download.media_id` (`media_id`),
    KEY `fk.acris_gprs_product_download.product_id` (`product_id`,`product_version_id`),
    KEY `fk.acris_gprs_product_download.preview_media_id` (`preview_media_id`),
    CONSTRAINT `fk.acris_gprs_product_download.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.acris_gprs_product_download.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `fk.acris_gprs_product_download.preview_media_id` FOREIGN KEY (`preview_media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        if (!$this->columnExists($connection, 'product', 'acrisGpsrDownloads')) {
            $this->updateInheritance($connection, 'product', 'acrisGpsrDownloads');
        }

        if (!$this->columnExists($connection, 'media', 'acrisGpsrDownloads')) {
            $this->updateInheritance($connection, 'media', 'acrisGpsrDownloads');
        }

    }


    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
