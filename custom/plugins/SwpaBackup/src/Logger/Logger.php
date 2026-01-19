<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Logger;

use Monolog\DateTimeImmutable;
use Monolog\Level;
use Monolog\Logger as BaseLogger;

/**
 * Logger
 *
 * @package   Swpa\SwpaBackup\Logger
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class Logger extends BaseLogger
{
    
    /**
     * Critical messages.
     *
     * @var array
     */
    private array $criticalStack = [];
    
    /**
     * Rewrite parent method, to prevent notice if the message is not scalar
     *
     * Adds a log record.
     *
     * @param int|Level $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     * @param DateTimeImmutable|null $datetime
     * @return Boolean Whether the record has been processed
     */
    public function addRecord(int|Level $level, string $message, array $context = [], DateTimeImmutable $datetime = null): bool
    {
        if (!is_scalar($message)) {
            $message = print_r($message, true);
        }
        
        return parent::addRecord($level, $message, $context);
    }
    
    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->criticalStack[md5($message)] = ['message' => $message, 'context' => $context];
        
        parent::critical($message, $context);
    }
    
    /**
     * Get critical notifications
     *
     * @return array
     */
    public function getCriticalNotifications(): array
    {
        return $this->criticalStack;
    }
}
