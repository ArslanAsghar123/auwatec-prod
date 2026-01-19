<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Tasks\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Swpa\SwpaBackup\Tasks\TaskInterface;

/**
 * Delete directory
 *
 * @package Swpa\SwpaBackup\Tasks\Storage
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class DeleteDir implements TaskInterface
{
    
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string     $dirPath
    )
    {
    }
    
    /**
     * @throws FilesystemException
     */
    public function execute(LoggerInterface $logger): void
    {
        $this->filesystem->deleteDirectory($this->dirPath);
    }
}
