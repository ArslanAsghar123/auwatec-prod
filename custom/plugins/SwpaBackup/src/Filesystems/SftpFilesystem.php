<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;

/**
 * SFTP Filesystem
 *
 * @package   Swpa\SwpaBackup\Filesystems
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class SftpFilesystem implements FilesystemAdapterInterface
{
    
    /**
     * @param string $type
     * @return bool
     */
    public function handles(string $type): bool
    {
        return strtolower($type) == 'sftp';
    }
    
    public function get(array $config): Filesystem
    {
        $adapter = new SftpAdapter(
            new SftpConnectionProvider(
                strval($config['host']),
                strval($config['username']),
                strval($config['password']),
                null,
                null,
                intval($config['port']),
                false,
                intval($config['timeout']),
            ),
            $config['root'],
        );
        return new Filesystem($adapter);
    }
}
