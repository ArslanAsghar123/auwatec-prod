<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Tasks;

use Psr\Log\LoggerInterface;

/**
 * Interface for Task classes
 *
 * @package Swpa\SwpaBackup\Tasks
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
interface TaskInterface
{
    public function execute(LoggerInterface $logger): void;
}
