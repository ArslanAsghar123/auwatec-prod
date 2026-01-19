<?php

namespace Swpa\SwpaBackup\Service\Archive;

interface ArchiveInterface
{
    /**
     * Pack file or directory.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function pack($source, $destination);
    
    /**
     * Unpack file or directory.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function unpack($source, $destination);
    
    public function setExcluded(array $directories);
}
