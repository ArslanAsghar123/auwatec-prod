<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup\Uninstall;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

/**
 * Uninstall schema
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Schema
{
    public function __construct(
        private readonly UninstallContext $context,
        private readonly Connection       $connection
    )
    {
    }
    
    public function uninstall(): void
    {
        $this->connection->executeUpdate("DROP TABLE IF EXISTS `swpa_backup`");
    }
}