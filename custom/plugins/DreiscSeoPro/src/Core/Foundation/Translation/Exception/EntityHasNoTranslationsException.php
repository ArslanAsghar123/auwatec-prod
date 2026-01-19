<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Translation\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class EntityHasNoTranslationsException extends DefaultException
{

    public function __construct(string $entityName)
    {
        parent::__construct(
            'The entity "{{ entityName }}" has no translations',
            [ 'entityName' => $entityName ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'ENTITY_HAS_NO_TRANSLATIONS';
    }
}
