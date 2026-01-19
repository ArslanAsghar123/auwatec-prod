<?php

namespace Swpa\SwpaBackup\Service\Archive;

class Zip extends AbstractArchive implements ArchiveInterface
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $type = 'Zip';
        if (!class_exists('\ZipArchive')) {
            throw new \Exception($type . ' file extension is not supported');
        }
    }
    
    /**
     * Pack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function pack($source, $destination)
    {
        $zip = new \ZipArchive();
        $zip->open($destination, \ZipArchive::CREATE);
        $zip->addFile($source);
        $zip->close();
        
        return $destination;
    }
    
    /**
     * Unpack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function unpack($source, $destination)
    {
        $zip = new \ZipArchive();
        $zip->open($source);
        $filename = $zip->getNameIndex(0);
        $zip->extractTo(dirname($destination), $filename);
        rename(dirname($destination) . '/' . $filename, $destination);
        $zip->close();
        
        return $destination;
    }
}
