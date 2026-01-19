<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Setup\Activate;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;

/**
 * Activate schema
 *
 * @package Swpa\SwpaBackup\Setup
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Schema
{
    public function __construct(
        private readonly ActivateContext $context,
        private readonly Connection      $connection
    )
    {
    }
    
    public function activate(): void
    {
    
    }
}
