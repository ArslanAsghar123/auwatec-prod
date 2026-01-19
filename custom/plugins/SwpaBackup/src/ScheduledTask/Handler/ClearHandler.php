<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\ScheduledTask\Handler;

use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Swpa\SwpaBackup\ScheduledTask\Clear;
use Swpa\SwpaBackup\Service\Config;
use Swpa\SwpaBackup\Service\Manager;

/**
 * Handler of task clear
 *
 * @package   Swpa\SwpaBackup\ScheduledTask\Handler
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class ClearHandler extends ScheduledTaskHandler
{
    public function __construct(
        protected EntityRepository $scheduledTaskRepository,
        protected LoggerInterface  $logger,
        protected Manager          $manager,
        protected Config           $config
    )
    {
        parent::__construct($scheduledTaskRepository);
    }
    
    /**
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [Clear::class];
    }
    
    /**
     *
     */
    public function run(): void
    {
        $this->logger->info('Clear handler: ' . static::class);
        try {
            $this->manager->clearBackup();
        } catch (FilesystemException|\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        
    }
}
