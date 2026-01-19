<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Tasks\Database;

use Psr\Log\LoggerInterface;
use Swpa\SwpaBackup\Databases\DatabaseConfigNotProvided;
use Swpa\SwpaBackup\Databases\DatabaseDumpNotCreated;
use Swpa\SwpaBackup\Databases\DatabaseInterface;
use Swpa\SwpaBackup\Tasks\TaskInterface;

/**
 * Create database dump
 *
 * @package Swpa\SwpaBackup\Tasks\Database
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class DumpDatabase implements TaskInterface
{
    public function __construct(private readonly DatabaseInterface $database, private readonly string $outputPath)
    {
    }
    
    /**
     * @throws DatabaseConfigNotProvided
     * @throws DatabaseDumpNotCreated
     */
    public function execute(LoggerInterface $logger): void
    {
        $this->database->dump($this->outputPath);
    }
}
