<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * task to clear old backups
 *
 * @package   Swpa\SwpaBackup\ScheduledTask
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class Clear extends ScheduledTask
{
    const TASK_NAME = 'swpa_backup.clear';
    
    const INTERVAL = 43200;
    
    /**
     * @return string
     */
    public static function getTaskName(): string
    {
        return static::TASK_NAME;
    }
    
    /**
     * @return int
     */
    public static function getDefaultInterval(): int
    {
        return static::INTERVAL;
    }
}
