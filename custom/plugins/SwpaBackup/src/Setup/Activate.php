<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Swpa\SwpaBackup\Setup\Activate\Schema;

/**
 * Activate
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Activate
{
    public function __construct(private readonly Connection $connection)
    {
    }
    
    public function activate(ActivateContext $context): void
    {
        $schema = new Schema($context, $this->connection);
        $schema->activate();
    }
}
