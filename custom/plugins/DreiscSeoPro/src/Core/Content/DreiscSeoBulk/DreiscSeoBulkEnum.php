<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk;

class DreiscSeoBulkEnum
{
    public const OVERWRITE__ALWAYS = 'always';
    public const OVERWRITE__EMPTY_AND_CUSTOM_FIELD_NOT_SET = 'emptyAndCustomFieldNotSet';
    public const OVERWRITE__EMPTY_OR_CUSTOM_FIELD_NOT_SET = 'emptyOrCustomFieldNotSet';

    public const VALID_BULK_GENERATOR_TYPES = [
        self::BULK_GENERATOR_TYPE__DEFAULT,
        self::BULK_GENERATOR_TYPE__AI
    ];
    public const BULK_GENERATOR_TYPE__AI = 'ai';
    public const BULK_GENERATOR_TYPE__DEFAULT = 'default';

    /** Areas */
    public const AREA__PRODUCT = 'product';
    public const AREA__CATEGORY = 'category';

    public const VALID_AREAS = [
        self::AREA__PRODUCT,
        self::AREA__CATEGORY
    ];

    /** Seo options */
    public const SEO_OPTION__META_TITLE = 'metaTitle';
    public const SEO_OPTION__META_DESCRIPTION = 'metaDescription';
    public const SEO_OPTION__URL = 'url';
    public const SEO_OPTION__ROBOTS_TAG = 'robotsTag';
    public const SEO_OPTION__FACEBOOK_TITLE = 'facebookTitle';
    public const SEO_OPTION__FACEBOOK_DESCRIPTION = 'facebookDescription';
    public const SEO_OPTION__TWITTER_TITLE = 'twitterTitle';
    public const SEO_OPTION__TWITTER_DESCRIPTION = 'twitterDescription';

    public const VALID_SEO_OPTIONS = [
        self::SEO_OPTION__META_TITLE,
        self::SEO_OPTION__META_DESCRIPTION,
        self::SEO_OPTION__URL,
        self::SEO_OPTION__ROBOTS_TAG,
        self::SEO_OPTION__FACEBOOK_TITLE,
        self::SEO_OPTION__FACEBOOK_DESCRIPTION,
        self::SEO_OPTION__TWITTER_TITLE,
        self::SEO_OPTION__TWITTER_DESCRIPTION
    ];

    public const SEO_OPTIONS_WHICH_REQUIRED_SALES_CHANNEL = [
        self::SEO_OPTION__URL
    ];
}
