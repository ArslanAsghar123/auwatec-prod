<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Translation\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class UnknownPropertyNameException extends DefaultException
{

                            /**
     * @param string $property
     */
    public function __construct(string $property)
    {
        parent::__construct(
            'The given property name "{{ property }}" does not exists for the entity',
            [ 'property' => $property ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'UNKNOWN_PROPERTY_NAME';
    }
}
