<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;

/**
 * Deactivate
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Deactivate
{
    public function __construct(private readonly Connection $connection)
    {
    }
    
    public function deactivate(DeactivateContext $context): void
    {
    }
}
