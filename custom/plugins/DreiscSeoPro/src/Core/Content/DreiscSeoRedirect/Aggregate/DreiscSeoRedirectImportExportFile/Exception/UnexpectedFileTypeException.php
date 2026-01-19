<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class UnexpectedFileTypeException extends DefaultException
{

                            /**
     * @param string $fileType
     */
    public function __construct(string $fileType)
    {
        parent::__construct(
            'Unexpected file type {{ fileType }}. "text/csv" is required.',
            [ 'fileType' => $fileType ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'UNEXPECTED_FILE_TYPE';
    }
}
