<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Swpa\SwpaBackup\Setup\Uninstall\Schema;

/**
 * Uninstall
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Uninstall
{
    public function __construct(private readonly Connection $connection)
    {
    }
    
    public function uninstall(UninstallContext $context): void
    {
        $schema = new Schema($context, $this->connection);
        $schema->uninstall();
    }
}