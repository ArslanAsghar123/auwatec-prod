<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup\Install;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Plugin\Context\InstallContext;

/**
 * Uninstall schema
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Schema
{
    use InheritanceUpdaterTrait;
    
    public function __construct(
        private readonly InstallContext $context,
        private readonly Connection     $connection
    )
    {
    }
    
    /**
     * @throws Exception
     */
    public function install(): void
    {
        
        $this->connection->executeStatement("CREATE TABLE IF NOT EXISTS `swpa_backup` (
        	`id` BINARY(16) NOT NULL,
        	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
        	`deleted` TINYINT(1) UNSIGNED NULL DEFAULT '0',
        	`filename` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
        	`filesystem` VARCHAR(10) NOT NULL COLLATE 'utf8mb4_unicode_ci',
        	`comment` TEXT NULL COLLATE 'utf8mb4_unicode_ci',
        	`created_at` DATETIME(3) NOT NULL,
        	`updated_at` DATETIME(3) NULL DEFAULT NULL,
        	PRIMARY KEY (`id`)
        ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB");
    }
    
}
