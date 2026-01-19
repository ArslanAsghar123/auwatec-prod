<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\ScheduledTask\Handler;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Swpa\SwpaBackup\ScheduledTask\Backup;
use Swpa\SwpaBackup\Service\Config;
use Swpa\SwpaBackup\Service\Manager;

/**
 * Handler of task backup
 *
 * @package   Swpa\SwpaBackup\ScheduledTask\Handler
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class BackupHandler extends ScheduledTaskHandler
{
    public function __construct(
        protected EntityRepository         $scheduledTaskRepository,
        protected readonly LoggerInterface $logger,
        protected readonly Manager         $manager,
        protected readonly Config          $config
    )
    {
        parent::__construct($scheduledTaskRepository);
    }
    
    /**
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [Backup::class];
    }
    
    /**
     *
     */
    public function run(): void
    {
        $this->logger->info('Backup handler: ' . static::class);
        
        try {
            $this->manager->makeBackup();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
