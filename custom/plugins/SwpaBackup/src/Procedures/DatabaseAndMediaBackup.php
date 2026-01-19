<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Procedures;

use Exception;
use League\Flysystem\FilesystemException;
use Swpa\SwpaBackup\Databases\DatabaseTypeNotSupported;
use Swpa\SwpaBackup\Filesystems\FilesystemTypeNotSupported;
use Swpa\SwpaBackup\Tasks;

/**
 * Procedure: database and media backup
 *
 * @package Swpa\SwpaBackup\Procedures
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class DatabaseAndMediaBackup extends Procedure
{
    
    /**
     * backup file prefix
     *
     * @var string
     */
    protected string $filePrefix = 'backup.media+database.';
    
    /**
     * run backup of database and  media
     *
     * @throws DatabaseTypeNotSupported
     * @throws FilesystemTypeNotSupported
     * @throws FilesystemException
     */
    public function run(): void
    {
        $this->start = microtime(true);
        $sequence = new Sequence($this->logger, $this->config);
        $this->logger->info('start procedure: backup media with database');
        
        // begin the life of a new working file
        $localFilesystem = $this->filesystemProvider->get('local');
        $dumpWorkingFile = $this->getWorkingFile('local');
        
        // create database backup file
        $sequence->add(new Tasks\Database\DumpDatabase(
            $this->databaseProvider->get('mysql'),
            $dumpWorkingFile
        ));
        
        $workingDirectory = $this->getWorkingDirectory();
        
        // create working directory
        if ($localFilesystem->has($workingDirectory)) {
            $localFilesystem->deleteDirectory($workingDirectory);
        }
        $localFilesystem->createDirectory($workingDirectory);
        
        // pack database dump an move it to temporary directory
        $sequence->add((new Tasks\Storage\Compress(
            $dumpWorkingFile,
            $this->prefixer->prefixPath($workingDirectory . '/database.tgz')
        )));
        
        // delete temporary backup file
        $sequence->add(new Tasks\Storage\DeleteFile($localFilesystem, basename($dumpWorkingFile)));
        
        $publicFilesystem = $this->filesystemProvider->get('public');
        
        // pack media to temporary directory
        $sequence->add((new Tasks\Storage\Compress(
            $publicFilesystem->applyPathPrefix('media'),
            $this->prefixer->prefixPath(basename($workingDirectory) . '/media.tgz')
        ))->setExcluded([]));
        
        $workingFile = $this->getWorkingFile('local') . '.tgz';
        
        // pack temporary directory
        $sequence->add(new Tasks\Storage\Compress(
            $this->prefixer->prefixPath(basename($workingDirectory)),
            $this->prefixer->prefixPath(basename($workingFile))
        ));
        
        // delete temporary directory
        $sequence->add(new Tasks\Storage\DeleteDir($localFilesystem, basename($workingDirectory)));
        
        $destinations = [];
        // move backup to destination
        foreach ($this->getDestinations() as $destination) {
            $sequence->add(new Tasks\Storage\TransferFile(
                $localFilesystem,
                basename($workingFile),
                $this->filesystemProvider->get($destination->destinationFilesystem()),
                $destination->destinationPath()
            ));
            $destinations[$destination->destinationFilesystem()] = $destination->destinationPath();
        }
        
        // remove backup file
        $sequence->add(new Tasks\Storage\DeleteFile($localFilesystem, basename($workingFile)));
        try {
            $sequence->execute();
            $this->addBackupToLog('Backup of Media and Database', true, $destinations);
        } catch (Exception $e) {
            $this->addBackupToLog('Backup of Media and Database. ERROR!: ' . $e->getMessage(), false, $destinations);
            unlink($workingFile);
        }
        
        $this->logger->info('end of procedure: backup media with database');
    }
    
}
