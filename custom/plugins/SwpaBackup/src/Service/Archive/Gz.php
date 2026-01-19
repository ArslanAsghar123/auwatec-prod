<?php

namespace Swpa\SwpaBackup\Service\Archive;

class Gz extends AbstractArchive implements ArchiveInterface
{
    /**
     * Pack file by GZ compressor.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function pack($source, $destination)
    {
        $fileReader = new \Swpa\SwpaBackup\Service\Archive\Helper\File($source);
        $fileReader->open('r');
        
        $archiveWriter = new \Swpa\SwpaBackup\Service\Archive\Helper\File\Gz($destination);
        $archiveWriter->open('wb9');
        
        while (!$fileReader->eof()) {
            $archiveWriter->write($fileReader->read());
        }
        
        $fileReader->close();
        $archiveWriter->close();
        
        return $destination;
    }
    
    /**
     * Unpack file by GZ compressor.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function unpack($source, $destination)
    {
        if (is_dir($destination)) {
            $file = $this->getFilename($source);
            $destination = $destination . $file;
        }
        
        $archiveReader = new \Swpa\SwpaBackup\Service\Archive\Helper\File\Gz($source);
        $archiveReader->open('r');
        
        $fileWriter = new \Swpa\SwpaBackup\Service\Archive\Helper\File($destination);
        $fileWriter->open('w');
        
        while (!$archiveReader->eof()) {
            $fileWriter->write($archiveReader->read());
        }
        
        return $destination;
    }
}
