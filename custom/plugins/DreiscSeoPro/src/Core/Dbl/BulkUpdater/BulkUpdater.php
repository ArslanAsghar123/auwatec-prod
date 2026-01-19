<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Dbl\BulkUpdater;

use DreiscSeoPro\Core\Dbl\PlainSqlUpdate\Category;
use DreiscSeoPro\Core\Dbl\PlainSqlUpdate\Product;

class BulkUpdater
{
    public function __construct(private readonly Product $productPlainSqlUpdater, private readonly Category $categoryPlainSqlUpdater)
    {
    }

    public function update(BulkUpdaterStruct $bulkUpdaterStruct): void
    {
        /** Abort, if there is no update */
        if(empty($bulkUpdaterStruct->getUpdates())) {
            return;
        }

        if (null === $bulkUpdaterStruct->getEntityRepository()) {
            throw new \RuntimeException('Entity repository is null');
        }

        /** Merge the updates by id */
        $updates = $this->mergeUpdates($bulkUpdaterStruct->getUpdates());

        /** Abort, if there is no update */
        if(empty($updates)) {
            return;
        }

        if (true) {
            match ($bulkUpdaterStruct->getEntityRepository()->getDefinition()->getEntityName()) {
                'product' => $this->productPlainSqlUpdater->update($updates),
                'category' => $this->categoryPlainSqlUpdater->update($updates),
                default => throw new \RuntimeException('Unhandled entity name: ' . $bulkUpdaterStruct->getEntityRepository()->getDefinition()->getEntityName()),
            };
        } else {
            /** Run the updates */
            $bulkUpdaterStruct->getEntityRepository()->update($updates);
        }
    }

    private function mergeUpdates(array $updates): array
    {
        $mergedUpdates = [];
        foreach($updates as $update) {
            /** Abort, if there is no id */
            if(empty($update['id'])) {
                continue;
            }

            /** Add to array, if id is not in array */
            if (empty($mergedUpdates[$update['id']])) {
                $mergedUpdates[$update['id']] = $update;
                continue;
            }

            /** Merge in existing entry */
            $mergedUpdates[$update['id']] = array_replace_recursive($mergedUpdates[$update['id']], $update);
        }

        /** Cleanup the keys */
        $mergedUpdates = array_values($mergedUpdates);

        return $mergedUpdates;
    }
}
