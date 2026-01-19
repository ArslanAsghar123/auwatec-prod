<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

/**
 * Destination class
 *
 * @package   Swpa\SwpaBackup\Filesystems
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
final class Destination
{
    public function __construct(
        private readonly string $destinationFilesystem,
        private readonly string $destinationPath
    )
    {
    }
    
    public function destinationFilesystem(): string
    {
        return $this->destinationFilesystem;
    }
    
    public function destinationPath(): string
    {
        return $this->destinationPath;
    }
}
