<?php declare(strict_types=1);

namespace IronMatomo\Framework\Cookie;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class CustomCookieProvider implements CookieProviderInterface {

    private CookieProviderInterface $originalService;
    public function __construct(
        CookieProviderInterface $service,
    )
    {
        $this->originalService = $service;
    }

    private const singleCookie = [
        'snippet_name' => 'IronMatomo.cookie.name',
        'snippet_description' => 'IronMatomo.cookie.description',
        'cookie' => 'ironMatomo',
        'value'=> 'active',
        'expiration' => '365'
    ];

    public function getCookieGroups(): array
    {
        return array_merge(
            $this->originalService->getCookieGroups(),
            [
                self::singleCookie
            ]
        );
    }
}
