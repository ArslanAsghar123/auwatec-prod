<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Tasks\Storage;

use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Swpa\SwpaBackup\Tasks\TaskInterface;

/**
 * Move file
 * @package Swpa\SwpaBackup\Tasks\Storage
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class TransferFile implements TaskInterface
{
    public function __construct(
        private readonly Filesystem $sourceFilesystem,
        private                     $sourcePath,
        private readonly Filesystem $destinationFilesystem,
        private                     $destinationPath
    )
    {
    }
    
    /**
     * @throws Exception
     */
    public function execute(LoggerInterface $logger): void
    {
        try {
            $this->destinationFilesystem->writeStream(
                $this->destinationPath,
                $this->sourceFilesystem->readStream($this->sourcePath)
            );
        } catch (Exception|FilesystemException $e) {
            throw new Exception("can't transfer file: " . $e->getMessage());
        }
    }
}