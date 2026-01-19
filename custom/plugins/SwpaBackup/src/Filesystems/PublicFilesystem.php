<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Class LocalFilesystem
 * @package BackupManager\Filesystems
 */
class PublicFilesystem implements FilesystemAdapterInterface
{
    
    /**
     * Test fitness of visitor.
     * @param $type
     * @return bool
     */
    public function handles($type): bool
    {
        return strtolower($type) == 'public';
    }
    
    public function get(array $config): Filesystem
    {
        $location = __DIR__ . '/../../../../../public/';
        return new Filesystem(new LocalFilesystemAdapter($location),$location);
    }
}
