<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Product;

class ProductEnum
{
    final public const CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__ITEM_CONDITION = 'dreisc_seo_rich_snippet_item_condition';
    final public const CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__AVAILABILITY = 'dreisc_seo_rich_snippet_availability';
    final public const CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__CUSTOM_SKU = 'dreisc_seo_rich_snippet_custom_sku';
    final public const CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__CUSTOM_MPN = 'dreisc_seo_rich_snippet_custom_mpn';
    final public const CUSTOM_FIELD__DREISC_SEO_RICH_SNIPPET__PRICE_VALID_UNTIL_DATE = 'dreisc_seo_rich_snippet_price_valid_until_date';

    final public const CUSTOM_FIELD__DREISC_SEO_ROBOTS_TAG = 'dreisc_seo_robots_tag';

    final public const CUSTOM_FIELD__DREISC_SEO_FACEBOOK_TITLE = 'dreisc_seo_facebook_title';
    final public const CUSTOM_FIELD__DREISC_SEO_FACEBOOK_DESCRIPTION = 'dreisc_seo_facebook_description';
    final public const CUSTOM_FIELD__DREISC_SEO_FACEBOOK_IMAGE = 'dreisc_seo_facebook_image';
    final public const CUSTOM_FIELD__DREISC_SEO_TWITTER_TITLE = 'dreisc_seo_twitter_title';
    final public const CUSTOM_FIELD__DREISC_SEO_TWITTER_DESCRIPTION = 'dreisc_seo_twitter_description';
    final public const CUSTOM_FIELD__DREISC_SEO_TWITTER_IMAGE = 'dreisc_seo_twitter_image';

    final public const CUSTOM_FIELD__DREISC_SEO_CANONICAL_LINK_TYPE = 'dreisc_seo_canonical_link_type';
    final public const CUSTOM_FIELD__DREISC_SEO_CANONICAL_LINK_REFERENCE = 'dreisc_seo_canonical_link_reference';

    final public const VALID_CANONICAL_LINK_TYPES = [
        ProductEnum::CANONICAL_LINK_TYPE__EXTERNAL_URL,
        ProductEnum::CANONICAL_LINK_TYPE__PRODUCT_URL,
        ProductEnum::CANONICAL_LINK_TYPE__CATEGORY_URL,
    ];
    final public const CANONICAL_LINK_TYPE__EXTERNAL_URL = 'ExternalUrl';
    final public const CANONICAL_LINK_TYPE__PRODUCT_URL = 'ProductUrl';
    final public const CANONICAL_LINK_TYPE__CATEGORY_URL = 'CategoryUrl';
}
