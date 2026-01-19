<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Local filesystem
 *
 * @package   Swpa\SwpaBackup\Filesystems
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class LocalFilesystem implements FilesystemAdapterInterface
{
    
    /**
     * @param string $type
     * @return bool
     */
    public function handles(string $type): bool
    {
        return strtolower($type) == 'local';
    }
    
    public function get(array $config): Filesystem
    {
        
        return new Filesystem(new LocalFilesystemAdapter($config['root']));
    }
}
