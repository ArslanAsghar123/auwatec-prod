<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class TemplateGeneratorException extends DefaultException
{
        
                                                                                        /**
     * @param string $errorMsg
     * @param string $templateName
     * @param string $area
     * @param string $seoOption
     */
    public function __construct(string $errorMsg, string $templateName, string $area, string $seoOption)
    {
        parent::__construct(
            'Error "{{ errorMsg }}" in template "{{ templateName }}". Area: "{{ area }}" / seo option: "{{ seoOption }}"',
            [ 'errorMsg' => $errorMsg, 'templateName' => $templateName, 'area' => $area, 'seoOption' => $seoOption ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'TEMPLATE_GENERATOR';
    }
}
