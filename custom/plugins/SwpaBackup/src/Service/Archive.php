<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Service;

use DateTime;
use Exception;
use Swpa\SwpaBackup\Service\Archive\Bz;
use Swpa\SwpaBackup\Service\Archive\Gz;
use Swpa\SwpaBackup\Service\Archive\Tar;

/**
 * Service to pack files in archive.
 * The archive is a directory in format BASE_DIR/archive/YYYY/MM/DD
 * the base dir can be defined through the method setDestinationDir
 *
 * @package   Swpa\SwpaBackup\Service
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class Archive
{
    /**
     * Archiver is used for compress.
     */
    const DEFAULT_ARCHIVER = 'gz';
    
    /**
     * Default packer for directory.
     */
    const TAPE_ARCHIVER = 'tar';
    
    /**
     * Current archiver is used for compress.
     *
     * @var Tar|Gz|Bz
     */
    protected $_archiver = null;
    
    /**
     * Accessible formats for compress.
     *
     * @var array
     */
    protected array $_formats = [
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
    
    const ARCHIVE_DIR_NAME = '\a\r\c\h\i\v\e\/Y/m/d';
    
    protected $baseDestinationDir;
    
    protected $baseDestinationArchiveDir;
    
    protected DateTime $clearTime;
    
    public function __construct()
    {
        $this->clearTime = new DateTime('now');
        $this->clearTime->modify('-1 month');
    }
    
    /**
     * The base directory, to create a archive directories in format archive/YYYY/MM/DD
     *
     * @param $dir
     *
     * @return $this
     * @throws Exception
     */
    public function setDestinationDir($dir)
    {
        if (!is_dir($dir)) {
            throw new Exception('the directory is not exist: ' . $dir);
        }
        $this->baseDestinationDir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->setArchiveDir();
        
        return $this;
    }
    
    /**
     * Set archive directory
     *
     * @param string|null $dir
     *
     * @throws Exception
     */
    protected function setArchiveDir(?string $dir = null): void
    {
        if (is_null($dir)) {
            $dir = $this->baseDestinationDir;
        }
        if (!is_dir($dir)) {
            throw new Exception('the directory is not exist: ' . $this->baseDestinationDir);
        }
        $this->baseDestinationArchiveDir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . date(
                static::ARCHIVE_DIR_NAME,
                time()
            ) . DIRECTORY_SEPARATOR;
        
        if (!is_dir($this->baseDestinationArchiveDir)) {
            mkdir($this->baseDestinationArchiveDir, 0777, true);
        }
    }
    
    /**
     * Create object of current archiver by $extension.
     *
     * @param string $extension
     *
     * @return Tar|Gz|Bz
     */
    protected function _getArchiver(string $extension): Bz|Tar|Gz
    {
        $extension = strtolower($extension);
        $format = $this->_formats[$extension] ?? self::DEFAULT_ARCHIVER;
        $class = '\\Swpa\SwpaBackup\Service\Archive\\' . ucfirst($format);
        $this->_archiver = new $class();
        
        return $this->_archiver;
    }
    
    /**
     * Split current format to list of archivers.
     *
     * @param string $source
     *
     * @return string[]|string
     */
    protected function _getArchivers(string $source): array|string
    {
        $ext = pathinfo($source, PATHINFO_EXTENSION);
        if (!empty($this->_formats[$ext])) {
            return explode('.', $this->_formats[$ext]);
        }
        
        return [];
    }
    
    /**
     * Pack file or directory to archivers are parsed from extension.
     *
     * @param string $source (full path)
     * @param string $destination (only file name)
     * @param boolean $skipRoot skip first level parent
     *
     * @return string Path to file
     * @throws Exception
     */
    public function pack(string $source, string $destination = 'packed.tgz', bool $skipRoot = false): string
    {
        if (empty($this->baseDestinationArchiveDir) || !is_dir($this->baseDestinationArchiveDir)) {
            throw new Exception('destination directory is not defined or not exist');
        }
        
        $destination = $this->baseDestinationArchiveDir . $destination;
        $archivers = $this->_getArchivers($destination);
        $interimSource = '';
        for ($i = 0; $i < count($archivers); $i++) {
            if ($i == count($archivers) - 1) {
                $packed = $destination;
            } else {
                $packed = dirname($destination) . '/~tmp-' . microtime(true) . $archivers[$i] . '.' . $archivers[$i];
            }
            $source = $this->_getArchiver($archivers[$i])->pack($source, $packed, $skipRoot);
            if ($interimSource && $i < count($archivers)) {
                unlink($interimSource);
            }
            $interimSource = $source;
        }
        
        $this->clearArchive();
        
        return $source;
    }
    
    /**
     * Clear archive
     *
     * will be removed all empty year's,month's,day's directories
     *
     * @throws Exception
     */
    private function clearArchive(): void
    {
        try {
            $collection = glob($this->baseDestinationDir . 'archive/*', GLOB_ONLYDIR);
            foreach ($collection as $dirYear) {
                if (!is_dir($dirYear)) {
                    continue;
                }
                if (!is_numeric(basename($dirYear)) || strlen(basename($dirYear)) != 4) {
                    continue;
                }
                
                $yearCollection = glob($dirYear . '/*', GLOB_ONLYDIR);
                if (count($yearCollection)) {
                    $this->clearMonths($dirYear, $yearCollection);
                    $yearCollection = glob($dirYear . '/*', GLOB_ONLYDIR);
                    if (count($yearCollection) == 0) {
                        rmdir($dirYear);
                    }
                    continue;
                }
                rmdir($dirYear);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * Clear old archive: months
     *
     * will be removed all empty month's,day's directories
     *
     * @param string $year
     * @param array $collection
     *
     * @throws Exception
     */
    private function clearMonths(string $year, array $collection): void
    {
        try {
            foreach ($collection as $month) {
                if (!is_dir($month)) {
                    continue;
                }
                if (!is_numeric(basename($month)) || strlen(basename($month)) != 2) {
                    continue;
                }
                $monthCollection = glob($month . '/*', GLOB_ONLYDIR);
                if (count($monthCollection) > 0) {
                    $this->clearDays($year, $month, $monthCollection);
                    $monthCollection = glob($month . '/*', GLOB_ONLYDIR);
                    if (count($monthCollection) == 0) {
                        rmdir($month);
                    }
                    continue;
                }
                rmdir($month);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * Clear old archive: days
     *
     * will be removed all day's directories with old archives
     *
     * @param string $year
     * @param string $month
     * @param array $collection
     *
     * @throws Exception
     */
    private function clearDays(string $year, string $month, array $collection): void
    {
        try {
            foreach ($collection as $day) {
                if (!is_dir($day)) {
                    continue;
                }
                if (!is_numeric(basename($day)) || strlen(basename($day)) != 2) {
                    continue;
                }
                $date = new DateTime((int)basename($year) . '-' . (int)basename($month) . '-' . (int)basename($day));
                if ($date < $this->clearTime) {
                    $dayCollection = glob($day . '/*');
                    foreach ($dayCollection as $file) {
                        unlink($file);
                    }
                    $dayCollection = glob($day . '/*');
                    if (empty($dayCollection)) {
                        rmdir($day);
                    }
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * Unpack file from archivers are parsed from extension.
     * If $tillTar == true unpack file from archivers till
     * meet TAR archiver.
     *
     * @param string $source
     * @param string $destination
     * @param bool $tillTar
     * @param bool $clearInterm
     *
     * @return string Path to file
     */
    public function unpack(string $source, string $destination = '.', bool $tillTar = false, bool $clearInterm = true): string
    {
        $archivers = $this->_getArchivers($source);
        $interimSource = '';
        for ($i = count($archivers) - 1; $i >= 0; $i--) {
            if ($tillTar && $archivers[$i] == self::TAPE_ARCHIVER) {
                break;
            }
            if ($i == 0) {
                $packed = rtrim($destination, '/') . '/';
            } else {
                $packed = rtrim(
                        $destination,
                        '/'
                    ) . '/~tmp-' . microtime(
                        true
                    ) . $archivers[$i - 1] . '.' . $archivers[$i - 1];
            }
            $source = $this->_getArchiver($archivers[$i])->unpack($source, $packed);
            
            if ($clearInterm && $interimSource) {
                unlink($interimSource);
            }
            $interimSource = $source;
        }
        
        return $source;
    }
    
    /**
     * Extract one file from TAR (Tape Archiver).
     *
     * @param string $file
     * @param string $source
     * @param string $destination
     *
     * @return string Path to file
     */
    public function extract(string $file, string $source, string $destination = '.'): string
    {
        $tarFile = $this->unpack($source, $destination, true);
        $resFile = $this->_getArchiver(self::TAPE_ARCHIVER)->extract($file, $tarFile, $destination);
        if (!$this->isTar($source)) {
            unlink($tarFile);
        }
        
        return $resFile;
    }
    
    /**
     * Check file is archive.
     *
     * @param string $file
     *
     * @return boolean
     */
    public function isArchive(string $file): bool
    {
        $archivers = $this->_getArchivers($file);
        return count($archivers) > 0;
    }
    
    /**
     * Check file is TAR.
     *
     * @param string $file
     *
     * @return boolean
     */
    public function isTar(string $file): bool
    {
        $archivers = $this->_getArchivers($file);
        if (count($archivers) == 1 && $archivers[0] == self::TAPE_ARCHIVER) {
            return true;
        }
        return false;
    }
}
