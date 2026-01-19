<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration9000000003CreateProductCustomFields extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 9_000_000_003;
    }

    public function update(Connection $connection): void
    {
        /** Try block to support the --keep-user-data uninstall */
        try {
            $connection->executeStatement("
                INSERT INTO `custom_field` (`id`, `name`, `type`, `config`, `active`, `set_id`, `created_at`, `updated_at`) VALUES
                (UNHEX('03A75808406545E8A7C3021041128AC3'),	'dreisc_seo_rich_snippet_availability',	'text',	NULL,	1,	NULL,	'2020-04-13 16:01:34.000',	NULL),
                (UNHEX('303D3E8A3B0690C44DE4EF9A912B99E2'),	'dreisc_seo_rich_snippet_custom_sku',	'text',	NULL,	1,	NULL,	'2020-04-13 16:01:50.000',	NULL),
                (UNHEX('39B68BE556C9C93BD7892692EC675998'),	'dreisc_seo_rich_snippet_price_valid_until_date',	'datetime',	NULL,	1,	NULL,	'2020-04-13 16:02:39.000',	NULL),
                (UNHEX('5E2D5D460191C9C8DA13B9B23AC45DF3'),	'dreisc_seo_rich_snippet_item_condition',	'text',	NULL,	1,	NULL,	'2020-04-13 11:39:32.000',	NULL),
                (UNHEX('73B3281850609D43678868317B9ACDB9'),	'dreisc_seo_rich_snippet_custom_mpn',	'text',	NULL,	1,	NULL,	'2020-04-13 16:02:07.000',	NULL);

            ");
        } catch (\Exception) { }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
