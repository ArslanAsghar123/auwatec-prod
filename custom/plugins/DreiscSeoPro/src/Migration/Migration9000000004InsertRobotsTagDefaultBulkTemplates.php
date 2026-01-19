<?php declare(strict_types=1);

namespace DreiscSeoPro\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration9000000004InsertRobotsTagDefaultBulkTemplates extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return (int) 9_000_000_004;
    }

    public function update(Connection $connection): void
    {
        /** Try block to support the --keep-user-data uninstall */
        try {
            $connection->executeStatement("
                INSERT INTO `dreisc_seo_bulk_template` (`id`, `area`, `seo_option`, `name`, `spaceless`, `template`, `created_at`, `updated_at`) VALUES
                (UNHEX('5FEC23475E8B416F9A07F9813AFF63C1'),	'category',	'robotsTag',	'dreiscSeoBulkCategory.defaultTemplates.robotsTag.noindexFollow',	1,	'noindex,follow',	'2020-05-14 10:25:00.006',	NULL),
                (UNHEX('B1C060C0FC794D60823A94969764A49E'),	'category',	'robotsTag',	'dreiscSeoBulkCategory.defaultTemplates.robotsTag.noindexNofollow',	1,	'noindex,nofollow',	'2020-05-14 10:25:13.589',	'2020-05-14 10:25:25.603'),
                (UNHEX('BEF4628027DD41EA9C6067BA12B5AAF5'),	'category',	'robotsTag',	'dreiscSeoBulkCategory.defaultTemplates.robotsTag.indexNofollow',	1,	'index,nofollow',	'2020-05-14 10:24:46.869',	NULL),
                (UNHEX('F5F96BE1E52042DE87FB994A5A111C18'),	'category',	'robotsTag',	'dreiscSeoBulkCategory.defaultTemplates.robotsTag.indexFollow',	1,	'index,follow',	'2020-05-14 10:24:34.615',	NULL),
                (UNHEX('06C90A5AACA7419DBA7BDD19C0825093'),	'product',	'robotsTag',	'dreiscSeoBulkProduct.defaultTemplates.robotsTag.indexFollow',	1,	'index,follow',	'2020-05-14 10:20:44.685',	NULL),
                (UNHEX('10D5700368544A1FADA0B575D2D8524B'),	'product',	'robotsTag',	'dreiscSeoBulkProduct.defaultTemplates.robotsTag.GrossPriceLessThanFive',	1,	'{# Base gross price without considering the product context #}\n{% set basePriceGross = product.price.elements[systemDefaults.CURRENCY].gross %}\n\n{% if basePriceGross < 5 %}\n    noindex,follow\n{% else %}\n    index,follow\n{% endif %}',	'2020-05-14 13:05:59.875',	'2020-05-14 13:13:57.426'),
                (UNHEX('9CA1AE7BCBD54CBFADF7DAD66E0CD8BF'),	'product',	'robotsTag',	'dreiscSeoBulkProduct.defaultTemplates.robotsTag.noindexFollow',	1,	'noindex,follow',	'2020-05-14 10:21:15.248',	NULL),
                (UNHEX('C550B59009DC4CF59EE75814459DE951'),	'product',	'robotsTag',	'dreiscSeoBulkProduct.defaultTemplates.robotsTag.noindexNofollow',	1,	'noindex,nofollow',	'2020-05-14 10:21:28.129',	NULL),
                (UNHEX('E9BA9E0B61DC412F854E2084547B0A52'),	'product',	'robotsTag',	'dreiscSeoBulkProduct.defaultTemplates.robotsTag.indexNofollow',	1,	'index,nofollow',	'2020-05-14 10:20:59.120',	NULL);
            ");
        } catch (\Exception) { }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
