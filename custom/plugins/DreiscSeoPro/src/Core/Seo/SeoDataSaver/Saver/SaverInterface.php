<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Saver;

use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;

interface SaverInterface
{
    /**
     * @param string $referenceId
     * @param string $languageId
     * @param string|null $salesChannelId
     * @param string $newValue
     * @param BulkUpdaterStruct|null $bulkUpdaterStruct
     */
    public function save(string $referenceId, string $languageId, ?string $salesChannelId, string $newValue, ?BulkUpdaterStruct $bulkUpdaterStruct = null): void;
}
