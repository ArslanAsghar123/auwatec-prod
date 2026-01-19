<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect;

class DreiscSeoRedirectEnum
{
    final public const REDIRECT_HTTP_STATUS_CODE__301 = '301';
    final public const REDIRECT_HTTP_STATUS_CODE__302 = '302';
    final public const VALID__REDIRECT_HTTP_STATUS_CODES = [
        self::REDIRECT_HTTP_STATUS_CODE__301,
        self::REDIRECT_HTTP_STATUS_CODE__302
    ];

    final public const SOURCE_TYPE__URL = 'url';
    final public const SOURCE_TYPE__PRODUCT = 'product';
    final public const SOURCE_TYPE__CATEGORY = 'category';
    final public const VALID_SOURCE_TYPES = [
        self::SOURCE_TYPE__URL,
        self::SOURCE_TYPE__PRODUCT,
        self::SOURCE_TYPE__CATEGORY
    ];

    final public const REDIRECT_TYPE__URL = 'url';
    final public const REDIRECT_TYPE__EXTERNAL_URL = 'externalUrl';
    final public const REDIRECT_TYPE__PRODUCT = 'product';
    final public const REDIRECT_TYPE__CATEGORY = 'category';
    final public const VALID_REDIRECT_TYPES = [
        self::REDIRECT_TYPE__URL,
        self::REDIRECT_TYPE__EXTERNAL_URL,
        self::REDIRECT_TYPE__PRODUCT,
        self::REDIRECT_TYPE__CATEGORY
    ];
}
