<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Service;

use DateTime;
use Doctrine\DBAL\Connection;
use Exception;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Swpa\SwpaBackup\DAL\Backup\SwpaBackupEntity;
use Swpa\SwpaBackup\Databases\DatabaseProvider;
use Swpa\SwpaBackup\Filesystems\FilesystemProvider;
use Swpa\SwpaBackup\Filesystems\FilesystemTypeNotSupported;
use Swpa\SwpaBackup\Procedures;
use Swpa\SwpaBackup\ScheduledTask\Backup;
use Swpa\SwpaBackup\ScheduledTask\Clear;

/**
 * Backup manager
 *
 * @package   Swpa\SwpaBackup\Service
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class Manager
{
    private static array $frequencyModification = [
        'daily' => '+1 day',
        'weekly' => '+1 week',
        'monthly' => '+1 month'
    ];
    
    public function __construct(
        private readonly FilesystemProvider $filesystems,
        private readonly DatabaseProvider   $databases,
        private readonly Config             $config,
        private readonly LoggerInterface    $logger,
        private readonly EntityRepository   $entityRepository,
        private readonly Connection         $connection
    )
    {
    }
    
    /**
     * clear old backups
     * @throws FilesystemTypeNotSupported|FilesystemException
     */
    public function clearBackup(): int
    {
        if (!$this->config->isClearBackupsEnabled() || !$this->config->isBackupEnabled()) {
            return 0;
        }
        $this->logger->info('-------------------------- run clear backup ------------------------------');
        $date = $this->getDateTime();
        $criteria = new Criteria();
        $criteria->addFilter(new RangeFilter('createdAt', [RangeFilter::LT => $date->format('Y-m-d H:i:s')]));
        $criteria->addFilter(new EqualsFilter('status', true));
        $criteria->addFilter(new EqualsFilter('deleted', SwpaBackupEntity::STATUS_DELETED_NON));
        $result = $this->entityRepository->search($criteria, $this->getContext());
        
        if ($result->getTotal() <= 0) {
            $this->logger->info('nothing to do');
            $this->logger->info('----------------------- stop clear backup -------------------------');
            return 1;
        }
        
        /** @var SwpaBackupEntity $entity */
        foreach ($result->getEntities() as $entity) {
            $this->logger->info('backup: ' . $entity->getFilename());
            $filesystem = $this->filesystems->get($entity->getFilesystem());
            if (!$filesystem->has($entity->getFilename())) {
                $this->logger->error("file {$entity->getFilename()} not found");
                $this->entityRepository->upsert([[
                    'id' => $entity->getId(),
                    'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                    'deleted' => SwpaBackupEntity::STATUS_DELETED_ERROR
                ]], $this->getContext());
                continue;
            }
            try {
                $filesystem->delete($entity->getFilename());
                $this->entityRepository->upsert([[
                    'id' => $entity->getId(),
                    'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                    'deleted' => SwpaBackupEntity::STATUS_DELETED_SUCCESS
                ]], $this->getContext());
                $this->logger->info('deleted successfully');
            } catch (Exception $e) {
                $this->logger->critical("cannot delete file {$entity->getFilename()}: " . $e->getMessage());
            }
        }
        
        $this->logger->info('----------------------- stop clear backup -------------------------');
        return 1;
    }
    
    /**
     * reschedule tasks
     * @throws \Doctrine\DBAL\Exception
     */
    public function checkScheduledTasks(): void
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->from('scheduled_task')
            ->select('*')
            ->where('scheduled_task_class=:className');
        
        $builder->setParameter('className', Backup::class);
        $result = $builder->executeQuery()->fetchAssociative();
        if (empty($result) || $result['status'] == 'scheduled') {
            return;
        }
        $this->connection->update('scheduled_task', ['status' => 'scheduled'], ['scheduled_task_class' => Backup::class]);
        $this->connection->update('scheduled_task', ['status' => 'scheduled'], ['scheduled_task_class' => Clear::class]);
    }
    
    /**
     * make backup
     *
     * @param bool $force
     * @return bool|void
     * @throws ConfigException
     */
    public function makeBackup(bool $force = false)
    {
        $this->logger->info('-------------------------- run backup ------------------------------');
        
        if ($force !== true && !$this->ready()) {
            return false;
        }
        
        if (!$this->config->isBackupEnabled()) {
            $this->logger->info('backup is disabled');
            $this->logger->info('----------------------- stop -------------------------');
            return;
        }
        
        $this->config->enableMaintenanceMode();
        ini_set('xdebug.max_nesting_level', '2048');
        ini_set('max_execution_time', '900');
        ignore_user_abort(true);
        set_time_limit(0);
        
        $procedure = match ($this->config->getBackupType()) {
            Config::TYPE_BACKUP_DATABASE_MEDIA => $this->makeMediaAndDatabaseBackup(),
            Config::TYPE_BACKUP_SYSTEM => $this->makeSystemBackup(),
            Config::TYPE_BACKUP_SYSTEM_WITHOUT_MEDIA => $this->makeSystemWithoutMediaBackup(),
            Config::TYPE_BACKUP_DATABASE => $this->makeDatabaseBackup(),
            default => null,
        };
        try {
            $procedure?->run();
        } catch (FilesystemException|Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        
        $this->config->ping();
        $this->config->disableMaintenanceMode();
        $this->logger->info('----------------------- stop -------------------------');
    }
    
    /**
     * check if the next launch is possible
     *
     * @return bool
     * @throws ConfigException
     * @throws Exception
     */
    protected function ready(): bool
    {
        $runTime = $this->config->getRunTime();
        $runFrequency = $this->config->getRunFrequency();
        $latestBackup = $this->getLatestBackup();
        if (!$latestBackup) {
            return true;
        }
        $current = new DateTime();
        $latest = new DateTime($latestBackup->getCreatedAt()->format('Y-m-d H:i:s'));
        $latest->modify(static::$frequencyModification[$runFrequency]);
        foreach ($runTime as $time) {
            $tempLatest = clone $latest;
            list($hour, $minute) = explode(':', $time);
            $tempLatest->setTime(intval($hour), intval($minute));
            $this->logger->info('the next launch is scheduled for: ' . $tempLatest->format('Y-m-d H:i:s'));
            if ($tempLatest <= $current) {
                return true;
            }
        }
        $this->logger->info('------------------------- stop ---------------------------');
        return false;
    }
    
    /**
     * get latest successfully backup
     *
     * @return SwpaBackupEntity|null
     */
    protected function getLatestBackup(): ?SwpaBackupEntity
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $result = $this->entityRepository->search($criteria, $this->getContext());
        if ($result->getTotal() == 0) {
            return null;
        }
        return $result->first();
    }
    
    /**
     * make database backup
     *
     * @return Procedures\DatabaseBackup
     */
    protected function makeDatabaseBackup(): Procedures\DatabaseBackup
    {
        $this->logger->info('current backup type: database');
        
        return new Procedures\DatabaseBackup(
            $this->filesystems,
            $this->databases,
            $this->config,
            $this->logger,
            $this->entityRepository,
            $this->connection
        );
    }
    
    /**
     * make media and database backup
     *
     * @return Procedures\DatabaseAndMediaBackup
     */
    protected function makeMediaAndDatabaseBackup(): Procedures\DatabaseAndMediaBackup
    {
        $this->logger->info('current backup type: database + media');
        
        return new Procedures\DatabaseAndMediaBackup(
            $this->filesystems,
            $this->databases,
            $this->config,
            $this->logger,
            $this->entityRepository,
            $this->connection
        );
    }
    
    /**
     * make system backup
     *
     * @return Procedures\SystemBackup
     */
    protected function makeSystemBackup(): Procedures\SystemBackup
    {
        $this->logger->info('current backup type: system');
        
        return new Procedures\SystemBackup(
            $this->filesystems,
            $this->databases,
            $this->config,
            $this->logger,
            $this->entityRepository,
            $this->connection
        );
    }
    
    /**
     * make system and media backup
     * @return Procedures\SystemWithoutMediaBackup
     */
    protected function makeSystemWithoutMediaBackup(): Procedures\SystemWithoutMediaBackup
    {
        $this->logger->info('current backup type: system (without media)');
        
        return new Procedures\SystemWithoutMediaBackup(
            $this->filesystems,
            $this->databases,
            $this->config,
            $this->logger,
            $this->entityRepository,
            $this->connection
        );
    }
    
    private function getContext(): Context
    {
        return Context::createDefaultContext();
    }
    
    /**
     * @return DateTime
     */
    private function getDateTime(): DateTime
    {
        $date = new DateTime();
        $savePeriod = $this->config->getCleanPeriod();
        if ($savePeriod == 10) {
            $date->modify("-1 week");
        } else {
            $date->modify("-$savePeriod month");
        }
        return $date;
    }
    
}
