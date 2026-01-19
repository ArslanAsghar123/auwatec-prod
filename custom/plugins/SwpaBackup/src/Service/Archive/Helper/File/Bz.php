<?php

namespace Swpa\SwpaBackup\Service\Archive\Helper\File;

class Bz extends \Swpa\SwpaBackup\Service\Archive\Helper\File
{
    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function _open($mode)
    {
        if (!extension_loaded('bz2')) {
            throw new \RuntimeException('PHP extension bz2 is required.');
        }
        $this->_fileHandler = bzopen($this->_filePath, $mode);
        
        if (false === $this->_fileHandler) {
            throw new \Exception('Failed to open file ' . $this->_filePath);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _write($data)
    {
        $result = bzwrite($this->_fileHandler, $data);
        
        if (false === $result) {
            throw new \Exception('Failed to write data to ' . $this->_filePath);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _read($length)
    {
        $data = bzread($this->_fileHandler, $length);
        
        if (false === $data) {
            throw new \Exception('Failed to read data from ' . $this->_filePath);
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _close()
    {
        bzclose($this->_fileHandler);
    }
}
