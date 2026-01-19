<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration9000000007BulkCustomFields extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 9_000_000_007;
    }

    public function update(Connection $connection): void
    {
        try {
            $connection->executeStatement("
                INSERT INTO `custom_field_set` (`id`, `name`, `config`, `active`, `app_id`, `position`, `global`, `created_at`, `updated_at`) VALUES
                (UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'dreisc_seo_custom_',	'{\"label\": {\"en-GB\": \"SEO Professional Bulk\"}}',	1,	NULL,	1,	0,	NOW(),	NULL);
            ");
        } catch (\Exception) { }

        try {
            $connection->executeStatement("
                INSERT INTO `custom_field_set_relation` (`id`, `set_id`, `entity_name`, `created_at`, `updated_at`) VALUES
                (UNHEX('0191B78ACF2975158501EA62B79DABB2'),	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'category',	NOW(),	NULL),
                (UNHEX('0191B78ACF2975158501EA63A94BA987'),	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'product',	NOW(),	NULL);
            ");
        } catch (\Exception) { }

        try {
            $connection->executeStatement("
                INSERT INTO `custom_field` (`id`, `name`, `type`, `config`, `active`, `set_id`, `created_at`, `updated_at`, `allow_customer_write`, `allow_cart_expose`) VALUES
                (UNHEX('0191B7941455778F8F248EFD29B0BA40'),	'dreisc_seo_custom__meta_title_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"Meta title generated\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 1}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('0191465120F9722F993140F9ADBCFF09'),	'dreisc_seo_custom__meta_description_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"Meta description generated\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 2}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('0191465132687252A9A25D4696526C74'),	'dreisc_seo_custom__url_main_shop_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"SEO-URL generated (Main shop)\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 3}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('019146513FAB73158BB47CE57B360B32'),	'dreisc_seo_custom__url_subshop_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"SEO-URL generated (Subshop 1)\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 4}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('019146514000707287A97BC64B88BD76'),	'dreisc_seo_custom__url_second_subshop_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"SEO-URL generated (Subshop 2)\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 5}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('01914651472E70758418CF42CF6EB786'),	'dreisc_seo_custom__robots_tag_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"Robots tag generated\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 6}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('01914651476171D18315F17532E8DF54'),	'dreisc_seo_custom__facebook_title_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"Facebook title generated\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 7}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('0191465147B070B9A17B6B030F178F33'),	'dreisc_seo_custom__facebook_description_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"Facebook description generated\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 8}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('0191465147FA73F7A26BE5F39D382BFA'),	'dreisc_seo_custom__twitter_title_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"Twitter title generated\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 9}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0),
                (UNHEX('01914651484871DEAE0D8782CDDE9C5B'),	'dreisc_seo_custom__twitter_description_generated',	'bool',	'{\"type\": \"checkbox\", \"label\": {\"en-GB\": \"Twitter description generated\"}, \"helpText\": {\"en-GB\": null}, \"componentName\": \"sw-field\", \"customFieldType\": \"checkbox\", \"customFieldPosition\": 9}',	1,	UNHEX('0191B78ACEFB7DE3BC962002D1060377'),	'2024-09-03 11:13:03.177',	NULL,	1,	0);
                ");
        } catch (\Exception) { }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
