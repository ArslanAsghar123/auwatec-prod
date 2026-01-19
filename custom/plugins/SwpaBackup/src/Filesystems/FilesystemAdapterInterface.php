<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

/**
 * Filesystem interface
 *
 * @package   Swpa\SwpaBackup\Filesystems
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
interface FilesystemAdapterInterface
{
    
    /**
     * @param string $type
     * @return bool
     */
    public function handles(string $type): bool;
    
    /**
     * @param array $config
     * @return Filesystem
     * @throws ClientConfigurationException
     */
    public function get(array $config): Filesystem;
}
