<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;

/**
 * FTP filesystem
 *
 * @package   Swpa\SwpaBackup\Filesystems
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class FtpFilesystem implements FilesystemAdapterInterface
{
    
    /**
     * @param string $type
     * @return bool
     */
    public function handles(string $type): bool
    {
        return strtolower($type) == 'ftp';
    }
    
    public function get(array $config): Filesystem
    {
        return new Filesystem(new FtpAdapter(FtpConnectionOptions::fromArray([
            'host' => $config['host'],
            'root' => $config['root'],
            'ssl' => true,
            'username' => $config['username'],
            'password' => $config['password'],
            'port' => $config['port'],
            'timeout' => $config['timeout'],
            'passive' => $config['passive'],
        ])));
    }
}
