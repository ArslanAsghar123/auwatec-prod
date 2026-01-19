<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\SeoUrl\SeoUrlRepository;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\CategorySeoDataFetcher;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\AbstractSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\SaverInterface;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;
use DreiscSeoPro\Core\Seo\SeoUrl\SeoUrlSaver;
use DreiscSeoPro\Core\Seo\SeoUrlSlugify;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;

class UrlSaver extends AbstractSaver
{
    public function __construct(private readonly CategorySeoDataFetcher $categorySeoDataFetcher, private readonly SeoUrlSaver $seoUrlSaver)
    {
    }

    public function isValid(SeoDataSaverStruct $seoDataSaverStruct, ?SeoDataFetchResultStruct $currentSeoDataFetchResultStruct): bool
    {
        if (null === $currentSeoDataFetchResultStruct) {
            return true;
        }

        return $this->isWritable(
            !(true === $currentSeoDataFetchResultStruct->isModifiedUrl() && null !== $currentSeoDataFetchResultStruct->getUrl() && false === $currentSeoDataFetchResultStruct->isInheritedUrl()),
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
//             * - url has the modified flag » An individual url was set before
//             * - url is not null AND
//             * - it's not an inherit value
//             */
//            if (true === $seoDataFetchResultStruct->isModifiedUrl() && null !== $seoDataFetchResultStruct->getUrl() && false === $seoDataFetchResultStruct->isInheritedUrl()) {
//                return;
//            }
//        }

        $this->seoUrlSaver->save(
            $languageId,
            $salesChannelId,
            $referenceId,
            SeoUrlRepository::ROUTE_NAME__FRONTEND_NAVIGATION_PAGE,
            $newValue
        );
    }
}
