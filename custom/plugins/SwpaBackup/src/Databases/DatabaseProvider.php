<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Databases;

use Swpa\SwpaBackup\Service\Config;

/**
 * Database provider
 *
 * @package   Swpa\SwpaBackup\Databases
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class DatabaseProvider
{
    private array $databases = [];
    
    /**
     * @param Config $config
     */
    public function __construct(private readonly Config $config)
    {
    }
    
    /**
     * @param DatabaseInterface $database
     */
    public function add(DatabaseInterface $database): void
    {
        $this->databases[] = $database;
    }
    
    /**
     * @param string $name
     * @return DatabaseInterface
     * @throws DatabaseTypeNotSupported
     */
    public function get(string $name): DatabaseInterface
    {
        foreach ($this->databases as $database) {
            if ($database->handles($name)) {
                return $database;
            }
        }
        throw new DatabaseTypeNotSupported("The requested database type {$name} is not currently supported.");
    }
}
