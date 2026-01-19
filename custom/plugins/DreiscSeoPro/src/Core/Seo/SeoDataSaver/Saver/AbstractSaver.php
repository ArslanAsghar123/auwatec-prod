<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Saver;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;

abstract class AbstractSaver implements SaverInterface
{
    public function isWritable(bool $seoFieldIsEmpty, SeoDataSaverStruct $seoDataSaverStruct, ?SeoDataFetchResultStruct $currentSeoDataFetchResultStruct): bool
    {
        if (DreiscSeoBulkEnum::OVERWRITE__ALWAYS === $seoDataSaverStruct->getOverwrite()) {
            return true;
        }

        if (DreiscSeoBulkEnum::OVERWRITE__EMPTY_AND_CUSTOM_FIELD_NOT_SET === $seoDataSaverStruct->getOverwrite()) {
            if(true !== $seoFieldIsEmpty || true === $seoDataSaverStruct->getOverwriteCustomFieldValue()) {
                return false;
            }

            return true;
        } elseif (DreiscSeoBulkEnum::OVERWRITE__EMPTY_OR_CUSTOM_FIELD_NOT_SET === $seoDataSaverStruct->getOverwrite()) {
            if(true !== $seoFieldIsEmpty && true === $seoDataSaverStruct->getOverwriteCustomFieldValue()) {
                return false;
            }

            return true;
        } else {
            return $seoFieldIsEmpty;
        }
    }
}
