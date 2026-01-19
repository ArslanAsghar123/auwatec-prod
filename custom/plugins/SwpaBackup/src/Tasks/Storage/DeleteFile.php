<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Tasks\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Swpa\SwpaBackup\Tasks\TaskInterface;

/**
 * Delete file
 *
 * @package Swpa\SwpaBackup\Tasks\Storage
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class DeleteFile implements TaskInterface
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private                     $filePath
    )
    {
    }
    
    /**
     * @throws FilesystemException
     */
    public function execute(LoggerInterface $logger): void
    {
        $this->filesystem->delete($this->filePath);
    }
}
