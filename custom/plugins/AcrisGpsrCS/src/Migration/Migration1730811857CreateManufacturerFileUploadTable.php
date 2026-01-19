<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1730811857CreateManufacturerFileUploadTable extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1730811857;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_manufacturer_download` (
    `id` BINARY(16) NOT NULL,
    `media_id` BINARY(16) NOT NULL,
    `acris_manufacturer_id` BINARY(16) NOT NULL,
    `position` INT(11) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `preview_image_enabled` TINYINT(1) NULL DEFAULT '0',
    `preview_media_id` BINARY(16) NULL,
    PRIMARY KEY (`id`),
    KEY `fk.acris_manufacturer_file.media_id` (`media_id`),
    KEY `fk.acris_manufacturer_file.acris_manufacturer_id` (`acris_manufacturer_id`),
    KEY `fk.acris_manufacturer_file.preview_media_id` (`preview_media_id`),
    CONSTRAINT `fk.acris_manufacturer_file.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.acris_manufacturer_file.acris_manufacturer_id` FOREIGN KEY (`acris_manufacturer_id`) REFERENCES `product_manufacturer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `fk.acris_manufacturer_file.preview_media_id` FOREIGN KEY (`preview_media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        if (!$this->columnExists($connection, 'product', 'acrisManufacturerDownloads')) {
            $this->updateInheritance($connection, 'product', 'acrisManufacturerDownloads');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
