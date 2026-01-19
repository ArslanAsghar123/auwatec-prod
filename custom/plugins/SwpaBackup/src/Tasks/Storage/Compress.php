<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Tasks\Storage;

use Psr\Log\LoggerInterface;
use Swpa\SwpaBackup\Service\Archive\ArchiveInterface;
use Swpa\SwpaBackup\Tasks\TaskInterface;

/**
 * Compress files or directory
 *
 * @package Swpa\SwpaBackup\Tasks\Storage
 * @license See COPYING.txt for license details
 * @author  swpa <info@swpa.dev>
 */
class Compress implements TaskInterface
{
    const DEFAULT_COMPRESSOR = 'gz';
    
    private array $availableFormats = [
        'tar' => 'tar',
        'gz' => 'gz',
        'gzip' => 'gz',
        'tgz' => 'tar.gz',
        'tgzip' => 'tar.gz',
        'bz' => 'bz',
        'bzip' => 'bz',
        'bzip2' => 'bz',
        'bz2' => 'bz',
        'tbz' => 'tar.bz',
        'tbzip' => 'tar.bz',
        'tbz2' => 'tar.bz',
        'tbzip2' => 'tar.bz',
    ];
    
    private array $excludedDirectories = [];
    
    public function __construct(
        private readonly string $sourcePath,
        private readonly string $destinationPath
    )
    {
    }
    
    /**
     * @param array $directories
     * @return TaskInterface
     */
    public function setExcluded(array $directories): TaskInterface
    {
        $this->excludedDirectories = $directories;
        return $this;
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function execute(LoggerInterface $logger): void
    {
        if ($this->verifyCommand('tar')) {
            $this->packTar();
            return;
        }
        // pack with PHP
        $compressors = $this->getCompressors($this->destinationPath);
        $source = $this->sourcePath;
        for ($i = 0; $i < count($compressors); $i++) {
            if ($i == count($compressors) - 1) {
                $packed = $this->destinationPath;
            } else {
                $packed = dirname($this->destinationPath) . '/~tmp-' . microtime(true) . $compressors[$i] . '.' . $compressors[$i];
            }
            $compressor = $this->getCompressor($compressors[$i]);
            if (!empty($this->excludedDirectories)) {
                $compressor->setExcluded($this->excludedDirectories);
            }
            $source = $compressor->pack($source, $packed);
        }
    }
    
    private function verifyCommand(string $command): bool
    {
        $windows = str_starts_with(PHP_OS, 'WIN');
        $test = $windows ? 'where' : 'command -v';
        $result = shell_exec("$test " . escapeshellarg($command));
        if (!$windows) {
            $helpResult = shell_exec("$command --help 2>&1");
            if (stripos($helpResult, 'Permission denied') !== false) {
                return false;
            }
        }
        return is_executable(trim($result ?: ''));
    }
    
    private function packTar(): void
    {
        $exclude = '';
        foreach ($this->excludedDirectories as $directory) {
            $directory = basename($directory);
            $exclude .= " --exclude=" . escapeshellarg($directory);
        }
        $exclude = trim($exclude);
        $pathInfo = pathinfo($this->sourcePath);
        $dirname = escapeshellarg($pathInfo['dirname']);
        $basename = escapeshellarg($pathInfo['basename']);
        $destinationPath = escapeshellarg($this->destinationPath);
        shell_exec("cd {$dirname} && tar {$exclude} -czPf {$destinationPath} {$basename} 2>&1");
    }
    
    protected function getCompressor($extension): ArchiveInterface
    {
        $extension = strtolower($extension);
        $format = $this->availableFormats[$extension] ?? self::DEFAULT_COMPRESSOR;
        $class = '\\Swpa\SwpaBackup\Service\Archive\\' . ucfirst($format);
        return new $class();
    }
    
    protected function getCompressors($source): array
    {
        $ext = pathinfo($source, PATHINFO_EXTENSION);
        if (!empty($this->availableFormats[$ext])) {
            return explode('.', $this->availableFormats[$ext]);
        }
        return [];
    }
}