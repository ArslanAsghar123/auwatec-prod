<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

use Swpa\SwpaBackup\Service\Config;
use Swpa\SwpaBackup\Service\ConfigException;

/**
 * Filesystem provider
 *
 * @package   Swpa\SwpaBackup\Filesystems
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class FilesystemProvider
{
    private array $filesystems = [];
    
    public function __construct(private readonly Config $config)
    {
    }
    
    /**
     * @param FilesystemAdapterInterface $filesystem
     */
    public function add(FilesystemAdapterInterface $filesystem): void
    {
        $this->filesystems[] = $filesystem;
    }
    
    /**
     * @throws FilesystemTypeNotSupported
     */
    public function get(string $name): Filesystem
    {
        /** @var FilesystemAdapterInterface $filesystem */
        foreach ($this->filesystems as $filesystem) {
            if ($filesystem->handles($name)) {
                if (!$config = $this->getConfig($name)) {
                    throw new FilesystemTypeNotSupported("The requested filesystem is not configured");
                }
                return $filesystem->get($config);
            }
        }
        throw new FilesystemTypeNotSupported("The requested filesystem type $name is not currently supported.");
    }
    
    /**
     * retrieve config of filesystem by name
     */
    public function getConfig(string $fileSystemName, ?string $key = null): array|string
    {
        try {
            return $this->config->getFilesystemConfig($fileSystemName, $key);
        } catch (ConfigException) {
            return [];
        }
    }
}
