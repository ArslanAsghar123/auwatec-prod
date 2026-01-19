<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class ReferenceEntityNotFoundException extends DefaultException
{

                                                /**
     * @param string $area
     * @param string $id
     */
    public function __construct(string $area, string $id)
    {
        parent::__construct(
            'The reference entity could not found » area:{{ area }}, id:{{ id }}',
            [ 'area' => $area, 'id' => $id ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'REFERENCE_ENTITY_NOT_FOUND';
    }
}
