<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class FileNotReadableException extends DefaultException
{
        
                            /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct(
            'Import file is not readable at {{ path }}',
            [ 'path' => $path ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'FILE_NOT_READABLE';
    }
}
