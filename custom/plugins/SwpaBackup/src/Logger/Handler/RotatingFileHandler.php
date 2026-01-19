<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Logger\Handler;

use DateTime;
use DateTimeImmutable;
use Exception;
use Monolog\Handler\RotatingFileHandler as BaseRotatingFileHandler;
use Swpa\SwpaBackup\Service\Archive;

/**
 * Handler to rotate files and add old files to archive
 *
 * @package   Swpa\SwpaBackup\Logger\Handler
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class RotatingFileHandler extends BaseRotatingFileHandler
{
    
    /**
     * @var Archive
     */
    private Archive $archive;
    
    /**
     * @param Archive $archive
     *
     * @return $this
     */
    public function setArchiveService(Archive $archive)
    {
        $this->archive = $archive;
        
        return $this;
    }
    
    /**
     * rewrite parent method, to add old files to archive
     * @throws Exception
     */
    protected function rotate(): void
    {
        // update filename
        $this->url = $this->getTimedFilename();
        $this->nextRotation = new DateTimeImmutable('tomorrow');
        
        // skip GC of old logs if files are unlimited
        if (0 === $this->maxFiles) {
            return;
        }
        
        $logFiles = glob($this->getGlobPattern());
        if ($this->maxFiles >= count($logFiles)) {
            // no files to remove
            return;
        }
        
        // Sorting the files by name to remove the older ones
        usort(
            $logFiles,
            function ($a, $b) {
                return strcmp($b, $a);
            }
        );
        
        foreach (array_slice($logFiles, $this->maxFiles) as $file) {
            preg_match('@.*?-(?P<year>[0-9]{4})-(?P<month>[0-9]{2})-(?P<day>[0-9]{2})@si', $file, $match);
            $logDate = new DateTime($match['year'] . '-' . $match['month'] . '-' . $match['day']);
            $clearToDate = new DateTime('now');
            $clearToDate->modify("-{$this->maxFiles} days");
            if ($logDate > $clearToDate) {
                continue;
            }
            // suppress errors here as unlink() might fail if two processes
            // are cleaning up/rotating at the same time
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            });
            $this->toArchive($file);
            restore_error_handler();
        }
        
        $this->mustRotate = false;
    }
    
    /**
     * add file to archive
     *
     * @param $file
     * @throws Exception
     */
    private function toArchive($file): void
    {
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $dir = pathinfo($file, PATHINFO_DIRNAME);
        $this->archive->setDestinationDir($dir)->pack($file, $filename . '.tgz');
        unlink($file);
    }
    
}
