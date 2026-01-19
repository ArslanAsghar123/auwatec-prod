<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Exception;

use DreiscSeoPro\Core\Foundation\Exception\DefaultException;

class UnknownSeoOptionException extends DefaultException
{
        
                            /**
     * @param string $seoOption
     */
    public function __construct(string $seoOption)
    {
        parent::__construct(
            'Unknown seo option "{{ seoOption }}"',
            [ 'seoOption' => $seoOption ]
        );
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'UNKNOWN_SEO_OPTION';
    }
}
