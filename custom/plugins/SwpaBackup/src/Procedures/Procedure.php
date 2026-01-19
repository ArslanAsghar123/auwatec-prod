<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Procedures;

use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use League\Flysystem\PathPrefixer;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context as CoreContext;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Swpa\SwpaBackup\Databases\DatabaseProvider;
use Swpa\SwpaBackup\Filesystems\Destination;
use Swpa\SwpaBackup\Filesystems\FilesystemProvider;
use Swpa\SwpaBackup\Service\Config;

/**
 * @package   Swpa\SwpaBackup\Procedures
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
abstract class Procedure
{
    protected string $filePrefix = 'backup.';
    protected float $start = 0;
    protected float $end = 0;
    protected PathPrefixer $prefixer;
    
    public function __construct(
        protected readonly FilesystemProvider $filesystemProvider,
        protected readonly DatabaseProvider   $databaseProvider,
        protected readonly Config             $config,
        protected readonly LoggerInterface    $logger,
        protected readonly EntityRepository   $entityRepository,
        protected readonly Connection         $connection
    )
    {
        $this->prefixer = new PathPrefixer($this->getRootPath('local'), DIRECTORY_SEPARATOR);
    }
    
    /**
     * run procedure
     */
    abstract public function run(): void;
    
    protected function getRootPath(string $name): string
    {
        $path = $this->filesystemProvider->getConfig($name, 'root');
        return preg_replace('/\/$/', '', $path);
    }
    
    protected function getWorkingFile(string $name, ?string $filename = null): string
    {
        if (is_null($filename)) {
            $filename = uniqid();
        }
        return sprintf('%s/%s', $this->getRootPath($name), $filename);
    }
    
    protected function getBackupFilename(): string
    {
        return $this->filePrefix . (new DateTime())->format('Y-m-d_H-i-s') . '.tgz';
    }
    
    protected function getDestinations(): array
    {
        $filename = $this->getBackupFilename();
        $destinations = [];
        foreach ($this->config->getDestinationFilesystems() as $name) {
            $destinations[] = new Destination($name, $filename);
        }
        return $destinations;
    }
    
    protected function getWorkingDirectory(): string
    {
        return uniqid();
    }
    
    protected function addBackupToLog(string $message, bool $status, array $destinations): void
    {
        $context = CoreContext::createDefaultContext();
        $data = [];
        $this->end = microtime(true);
        $time = $this->end - $this->start;
        foreach ($destinations as $filesystem => $filename) {
            $data[] = [
                'id' => Uuid::randomHex(),
                'comment' => $message,
                'filename' => $filename,
                'filesystem' => $filesystem,
                'time' => round(($time / 60), 2),
                'status' => $status ? 1 : 0
            ];
        }
        try {
            $this->reconnect();
            $this->entityRepository->upsert($data, $context);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
    
    /**
     * @throws Exception
     */
    private function reconnect(): void
    {
        try {
            $this->connection->close();
            $this->connection->connect();
        } catch (Exception $e) {
            $this->logger->critical('cannot reconnect to mysql');
            throw new Exception('cannot connect to mysql. ' . $e->getMessage());
        }
    }
}
