<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup\Deactivate;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;

/**
 * Deactivate schema
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Schema
{
    public function __construct(
        private readonly DeactivateContext $context,
        private readonly Connection        $connection
    )
    {
    }
    
    public function deactivate(): void
    {
    }
}