<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Logger\Handler;

use Monolog\LogRecord;

/**
 * The handler add ability to lock the script through flock() for all execution time
 *
 * each process, that use the handler is a singleton process
 *
 * @package   Swpa\SwpaBackup\Logger\Handler
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class RotatingFileWithLockHandler extends RotatingFileHandler
{
    
    private $locked = null;
    
    /**
     * Lock the current resource, it will be unlocked after stop of the script
     */
    protected function streamWrite($stream, array|LogRecord $record): void
    {
        if (!$this->locked) {
            flock($this->stream, LOCK_EX);
            $this->locked = true;
        }
        parent::streamWrite($stream, $record);
    }
}
