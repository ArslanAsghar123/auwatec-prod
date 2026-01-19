<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Logger\Formatter;

use Monolog\Formatter\LineFormatter;

/**
 * Formatter to write array or objects to log
 *
 * @package   Swpa\SwpaBackup\Logger\Formatter
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class ArrayFormatter extends LineFormatter
{
    
    public function stringify($value): string
    {
        return $this->convertToString($value);
    }
    
}
