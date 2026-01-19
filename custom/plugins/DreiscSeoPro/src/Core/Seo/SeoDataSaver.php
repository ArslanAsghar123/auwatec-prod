<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver\CategorySeoDataSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Exception\UnknownAreaException;
use DreiscSeoPro\Core\Seo\SeoDataSaver\ProductSeoDataSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;

class SeoDataSaver
{
    /**
     * @var CategorySeoDataSaver
     */
    protected $categorySeoDataSaver;

    /**
     * @var ProductSeoDataSaver
     */
    protected $productSeoDataSaver;

    public function __construct(CategorySeoDataSaver $categorySeoDataSaver, ProductSeoDataSaver $productSeoDataSaver)
    {
        $this->categorySeoDataSaver = $categorySeoDataSaver;
        $this->productSeoDataSaver = $productSeoDataSaver;
    }

    public function isValidItem(SeoDataSaverStruct $seoDataSaverStruct, ?SeoDataFetchResultStruct $currentSeoDataFetchResultStruct): bool
    {
        switch ($seoDataSaverStruct->getArea()) {
            case DreiscSeoBulkEnum::AREA__CATEGORY:
                return $this->categorySeoDataSaver->isValidItem($seoDataSaverStruct, $currentSeoDataFetchResultStruct);
                break;

            case DreiscSeoBulkEnum::AREA__PRODUCT:
                return $this->productSeoDataSaver->isValidItem($seoDataSaverStruct, $currentSeoDataFetchResultStruct);
                break;

            default:
                throw new UnknownAreaException($seoDataSaverStruct->getArea());
                break;
        }
    }

    /**
     * Saves the given SeoDataSaver structs
     */
    public function save(array $seoDataSaverStructs, ?BulkUpdaterStruct $bulkUpdaterStruct = null)
    {
        /** Sort the structs by area */
        $sortedSeoDataSaverStructs = [
            DreiscSeoBulkEnum::AREA__CATEGORY => [],
            DreiscSeoBulkEnum::AREA__PRODUCT => []
        ];

        foreach ($seoDataSaverStructs as $seoDataSaverStruct) {
            switch ($seoDataSaverStruct->getArea()) {
                case DreiscSeoBulkEnum::AREA__CATEGORY:
                    $sortedSeoDataSaverStructs[DreiscSeoBulkEnum::AREA__CATEGORY][] = $seoDataSaverStruct;
                    break;

                case DreiscSeoBulkEnum::AREA__PRODUCT:
                    $sortedSeoDataSaverStructs[DreiscSeoBulkEnum::AREA__PRODUCT][] = $seoDataSaverStruct;
                    break;

                default:
                    throw new UnknownAreaException($seoDataSaverStruct->getArea());
                    break;
            }
        }

        /** Save the categories */
        if(!empty($sortedSeoDataSaverStructs[DreiscSeoBulkEnum::AREA__CATEGORY])) {
            $this->categorySeoDataSaver->save($sortedSeoDataSaverStructs[DreiscSeoBulkEnum::AREA__CATEGORY], $bulkUpdaterStruct);
        }

        /** Save the products */
        if(!empty($sortedSeoDataSaverStructs[DreiscSeoBulkEnum::AREA__PRODUCT])) {
            $this->productSeoDataSaver->save($sortedSeoDataSaverStructs[DreiscSeoBulkEnum::AREA__PRODUCT], $bulkUpdaterStruct);
        }
    }
}
