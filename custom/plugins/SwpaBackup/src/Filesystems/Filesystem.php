<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Filesystems;

use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathNormalizer;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;

/**
 * Filesystem interface
 *
 * @package   Swpa\SwpaBackup\Filesystems
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class Filesystem extends LeagueFilesystem
{
    private PathPrefixer $prefixer;
    public function __construct(
        private FilesystemAdapter $adapter,
        ?string $location = null
    )
    {
        if($location) {
            $this->prefixer = new PathPrefixer($location, DIRECTORY_SEPARATOR);
        }
        parent::__construct($this->adapter, []);
    }
    
    public function applyPathPrefix(string $path) : string
    {
        return $this->prefixer->prefixPath($path);
    }
}
