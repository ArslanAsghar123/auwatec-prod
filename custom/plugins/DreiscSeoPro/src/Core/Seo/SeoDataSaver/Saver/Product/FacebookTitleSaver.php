<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Product;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\Product\ProductEnum;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\ProductSeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\AbstractSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\SaverInterface;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;

class FacebookTitleSaver extends AbstractSaver
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
            !(null !== $currentSeoDataFetchResultStruct->getFacebookTitle() && false === $currentSeoDataFetchResultStruct->isInheritedFacebookTitle()),
            $seoDataSaverStruct,
            $currentSeoDataFetchResultStruct
        );
    }

    /**
     * @throws DBALException
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
//             * - value is not null AND
//             * - it's not an inherit value
//             */
//            if (null !== $seoDataFetchResultStruct->getFacebookTitle() && false === $seoDataFetchResultStruct->isInheritedFacebookTitle()) {
//                return;
//            }
//        }

        $update = [
            'id' => $referenceId,
            'translations' => [
                $languageId => [
                    'customFields' => [
                        ProductEnum::CUSTOM_FIELD__DREISC_SEO_FACEBOOK_TITLE => $newValue
                    ]
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
