<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class UnknownAreaException extends DefaultException
{

                            /**
     * @param string $area
     */
    public function __construct(string $area)
    {
        parent::__construct(
            'Unknown area: {{ area }}',
            [ 'area' => $area ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'UNKNOWN_AREA';
    }
}
