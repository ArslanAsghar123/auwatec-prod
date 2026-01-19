<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Product;

use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\AbstractSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\SaverInterface;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;

class MetaDescriptionSaver extends AbstractSaver
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(private readonly ProductSeoDataFetcher $productSeoDataFetcher, ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function isValid(SeoDataSaverStruct $seoDataSaverStruct, ?SeoDataFetchResultStruct $currentSeoDataFetchResultStruct): bool
    {
        if (null === $currentSeoDataFetchResultStruct) {
            return true;
        }

        return $this->isWritable(
            !(null !== $currentSeoDataFetchResultStruct->getMetaDescription() && false === $currentSeoDataFetchResultStruct->isInheritedMetaDescription()),
            $seoDataSaverStruct,
            $currentSeoDataFetchResultStruct
        );
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save(string $referenceId, string $languageId, ?string $salesChannelId, string $newValue, ?BulkUpdaterStruct $bulkUpdaterStruct = null): void
    {
        /** We have to check whether there is already a value if this is not to be overwritten  */
//        if (false === $overwrite) {
//            /** Load the current values */
//            $seoDataFetchResultStruct = $this->productSeoDataFetcher->fetch($referenceId, $languageId, $salesChannelId);
//
//            /**
//             * Abort, if the value is already set:
//             * - meta description is not null AND
//             * - it's not an inherit value
//             */
//            if (null !== $seoDataFetchResultStruct->getMetaDescription() && false === $seoDataFetchResultStruct->isInheritedMetaDescription()) {
//                return;
//            }
//        }

        /** Shorten the character string to 255 characters if longer */
        if (mb_strlen($newValue) > 255) {
            $newValue = mb_substr($newValue, 0, 255);
        }

        $update = [
            'id' => $referenceId,
            'translations' => [
                $languageId => [
                    'metaDescription' => $newValue
                ]
            ]
        ];

        /** Add the update to the bulk updater, if available. Otherwise update directly */
        if (null !== $bulkUpdaterStruct) {
            $bulkUpdaterStruct->setEntityRepositoryIfNull($this->productRepository);
            $bulkUpdaterStruct->addUpdate($update);
        } else {
            $this->productRepository->update([
                $update
            ]);
        }
    }
}
