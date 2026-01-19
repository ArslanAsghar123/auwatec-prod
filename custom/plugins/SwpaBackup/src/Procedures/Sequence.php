<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Procedures;

use Exception;
use Psr\Log\LoggerInterface;
use Swpa\SwpaBackup\Service\Config;
use Swpa\SwpaBackup\Tasks\TaskInterface;

/**
 * Class Sequence
 * @package BackupManager\Procedures
 */
final class Sequence
{
    /** @var array|TaskInterface[] */
    private array $tasks = [];
    
    public function __construct(private readonly LoggerInterface $logger, private readonly Config $config)
    {
    }
    
    public function add(TaskInterface $task): void
    {
        $this->tasks[] = $task;
    }
    
    /**
     * run tasks
     *
     * @throws Exception
     */
    public function execute(): void
    {
        foreach ($this->tasks as $task) {
            $this->logger->info('-------------------------------------');
            $this->logger->info('run task: ' . get_class($task));
            try {
                $task->execute($this->logger);
                $this->logger->info('successfully');
                $this->config->ping($this->logger);
            } catch (Exception $e) {
                $this->logger->critical("cannot execute the task [" . get_class($task) . "]: " . $e->getMessage());
                throw new Exception('Brake on task: ' . get_class($task) . '. See log for more information');
            }
            $this->logger->info('-------------------------------------');
        }
    }
}
