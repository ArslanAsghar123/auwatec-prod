<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Databases;

/**
 * Database interface
 *
 * @package   Swpa\SwpaBackup\Databases
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
interface DatabaseInterface
{
    
    /**
     * @param string $type
     * @return bool
     */
    public function handles(string $type): bool;
    
    /**
     * @param string $workingFile
     * @throws DatabaseConfigNotProvided
     * @throws DatabaseDumpNotCreated
     */
    public function dump(string $workingFile): void;
    
}
