<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\Category\CategoryEnum;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\CategorySeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\AbstractSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\SaverInterface;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;

class RobotsTagSaver extends AbstractSaver
{
    public function __construct(private readonly CategorySeoDataFetcher $categorySeoDataFetcher, private readonly CategoryRepository $categoryRepository)
    {
    }

    public function isValid(SeoDataSaverStruct $seoDataSaverStruct, ?SeoDataFetchResultStruct $currentSeoDataFetchResultStruct): bool
    {
        if (null === $currentSeoDataFetchResultStruct) {
            return true;
        }

        return $this->isWritable(
            !(null !== $currentSeoDataFetchResultStruct->getRobotsTag() && false === $currentSeoDataFetchResultStruct->isInheritedRobotsTag()),
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
//            $seoDataFetchResultStruct = $this->categorySeoDataFetcher->fetch($referenceId, $languageId, $salesChannelId);
//
//            /**
//             * Abort, if the value is already set:
//             * - robots tag is not null AND
//             * - it's not an inherit value
//             */
//            if (null !== $seoDataFetchResultStruct->getRobotsTag() && false === $seoDataFetchResultStruct->isInheritedRobotsTag()) {
//                return;
//            }
//        }

        /** Update the meta title */
        $this->categoryRepository->update([
            [
                'id' => $referenceId,
                'translations' => [
                    $languageId => [
                        'customFields' => [
                            CategoryEnum::CUSTOM_FIELD__DREISC_SEO_ROBOTS_TAG => $newValue
                        ]
                    ]
                ]
            ]
        ]);
    }
}
