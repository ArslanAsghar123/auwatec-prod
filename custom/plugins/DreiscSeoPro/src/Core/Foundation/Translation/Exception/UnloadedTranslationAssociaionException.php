<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Translation\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class UnloadedTranslationAssociaionException extends DefaultException
{

    public function __construct(string $entityName)
    {
        parent::__construct(
            'The translation association was not loaded for the "{{ entityName }}" entity',
            [ 'entityName' => $entityName ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'UNLOADED_TRANSLATION_ASSOCIAION';
    }
}
