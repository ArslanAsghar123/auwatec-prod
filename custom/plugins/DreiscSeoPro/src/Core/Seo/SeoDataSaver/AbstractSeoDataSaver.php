<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Dbl\BulkUpdater\BulkUpdaterStruct;
use DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct\SeoDataFetchResultStruct;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Exception\UnknownSeoOptionException;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\SaverInterface;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Struct\SeoDataSaverStruct;

abstract class AbstractSeoDataSaver
{
    public function save(array $seoDataSaverStructs, ?BulkUpdaterStruct $bulkUpdaterStruct = null): void
    {
        foreach($seoDataSaverStructs as $seoDataSaverStruct) {
            /** Abort, if the area is not correct */
            if ($this->getArea() !== $seoDataSaverStruct->getArea()) {
                continue;
            }

            /** Save the item */
            $this->saveItem($seoDataSaverStruct, $bulkUpdaterStruct);
        }
    }

    public function isValidItem(SeoDataSaverStruct $seoDataSaverStruct, ?SeoDataFetchResultStruct $currentSeoDataFetchResultStruct): bool
    {
        switch ($seoDataSaverStruct->getSeoOption()) {
            case DreiscSeoBulkEnum::SEO_OPTION__META_TITLE:
                return $this->getMetaTitleSaver()->isValid(
                    $seoDataSaverStruct,
                    $currentSeoDataFetchResultStruct
                );
            case DreiscSeoBulkEnum::SEO_OPTION__META_DESCRIPTION:
                return $this->getMetaDescriptionSaver()->isValid(
                    $seoDataSaverStruct,
                    $currentSeoDataFetchResultStruct
                );
            case DreiscSeoBulkEnum::SEO_OPTION__URL:
                return $this->getUrlSaver()->isValid(
                    $seoDataSaverStruct,
                    $currentSeoDataFetchResultStruct
                );
            case DreiscSeoBulkEnum::SEO_OPTION__ROBOTS_TAG:
                return $this->getRobotsTagSaver()->isValid(
                    $seoDataSaverStruct,
                    $currentSeoDataFetchResultStruct
                );
            case DreiscSeoBulkEnum::SEO_OPTION__FACEBOOK_TITLE:
                return $this->getFacebookTitleSaver()->isValid(
                    $seoDataSaverStruct,
                    $currentSeoDataFetchResultStruct
                );
            case DreiscSeoBulkEnum::SEO_OPTION__FACEBOOK_DESCRIPTION:
                return $this->getFacebookDescriptionSaver()->isValid(
                    $seoDataSaverStruct,
                    $currentSeoDataFetchResultStruct
                );
            case DreiscSeoBulkEnum::SEO_OPTION__TWITTER_TITLE:
                return $this->getTwitterTitleSaver()->isValid(
                    $seoDataSaverStruct,
                    $currentSeoDataFetchResultStruct
                );
            case DreiscSeoBulkEnum::SEO_OPTION__TWITTER_DESCRIPTION:
                return $this->getTwitterDescriptionSaver()->isValid(
                    $seoDataSaverStruct,
                    $currentSeoDataFetchResultStruct
                );
            default:
                throw new UnknownSeoOptionException($seoDataSaverStruct->getSeoOption());
        }
    }

    private function saveItem(SeoDataSaverStruct $seoDataSaverStruct, ?BulkUpdaterStruct $bulkUpdaterStruct = null)
    {
        match ($seoDataSaverStruct->getSeoOption()) {
            DreiscSeoBulkEnum::SEO_OPTION__META_TITLE => $this->getMetaTitleSaver()->save(
                $seoDataSaverStruct->getReferenceId(),
                $seoDataSaverStruct->getLanguageId(),
                $seoDataSaverStruct->getSalesChannelId(),
                $seoDataSaverStruct->getNewValue(),
                $bulkUpdaterStruct
            ),
            DreiscSeoBulkEnum::SEO_OPTION__META_DESCRIPTION => $this->getMetaDescriptionSaver()->save(
                $seoDataSaverStruct->getReferenceId(),
                $seoDataSaverStruct->getLanguageId(),
                $seoDataSaverStruct->getSalesChannelId(),
                $seoDataSaverStruct->getNewValue(),
                $bulkUpdaterStruct
            ),
            DreiscSeoBulkEnum::SEO_OPTION__URL => $this->getUrlSaver()->save(
                $seoDataSaverStruct->getReferenceId(),
                $seoDataSaverStruct->getLanguageId(),
                $seoDataSaverStruct->getSalesChannelId(),
                $seoDataSaverStruct->getNewValue(),
                $bulkUpdaterStruct
            ),
            DreiscSeoBulkEnum::SEO_OPTION__ROBOTS_TAG => $this->getRobotsTagSaver()->save(
                $seoDataSaverStruct->getReferenceId(),
                $seoDataSaverStruct->getLanguageId(),
                $seoDataSaverStruct->getSalesChannelId(),
                $seoDataSaverStruct->getNewValue(),
                $bulkUpdaterStruct
            ),
            DreiscSeoBulkEnum::SEO_OPTION__FACEBOOK_TITLE => $this->getFacebookTitleSaver()->save(
                $seoDataSaverStruct->getReferenceId(),
                $seoDataSaverStruct->getLanguageId(),
                $seoDataSaverStruct->getSalesChannelId(),
                $seoDataSaverStruct->getNewValue(),
                $bulkUpdaterStruct
            ),
            DreiscSeoBulkEnum::SEO_OPTION__FACEBOOK_DESCRIPTION => $this->getFacebookDescriptionSaver()->save(
                $seoDataSaverStruct->getReferenceId(),
                $seoDataSaverStruct->getLanguageId(),
                $seoDataSaverStruct->getSalesChannelId(),
                $seoDataSaverStruct->getNewValue(),
                $bulkUpdaterStruct
            ),
            DreiscSeoBulkEnum::SEO_OPTION__TWITTER_TITLE => $this->getTwitterTitleSaver()->save(
                $seoDataSaverStruct->getReferenceId(),
                $seoDataSaverStruct->getLanguageId(),
                $seoDataSaverStruct->getSalesChannelId(),
                $seoDataSaverStruct->getNewValue(),
                $bulkUpdaterStruct
            ),
            DreiscSeoBulkEnum::SEO_OPTION__TWITTER_DESCRIPTION => $this->getTwitterDescriptionSaver()->save(
                $seoDataSaverStruct->getReferenceId(),
                $seoDataSaverStruct->getLanguageId(),
                $seoDataSaverStruct->getSalesChannelId(),
                $seoDataSaverStruct->getNewValue(),
                $bulkUpdaterStruct
            ),
            default => throw new UnknownSeoOptionException($seoDataSaverStruct->getSeoOption()),
        };
    }

    /**
     * @return string
     */
    abstract protected function getArea(): string;

    /**
     * @return SaverInterface
     */
    abstract protected function getMetaTitleSaver(): SaverInterface;

    /**
     * @return SaverInterface
     */
    abstract protected function getMetaDescriptionSaver(): SaverInterface;

    /**
     * @return SaverInterface
     */
    abstract protected function getUrlSaver(): SaverInterface;

    /**
     * @return SaverInterface
     */
    abstract protected function getRobotsTagSaver(): SaverInterface;

    /**
     * @return SaverInterface
     */
    abstract protected function getFacebookTitleSaver(): SaverInterface;

    /**
     * @return SaverInterface
     */
    abstract protected function getFacebookDescriptionSaver(): SaverInterface;

    /**
     * @return SaverInterface
     */
    abstract protected function getTwitterTitleSaver(): SaverInterface;

    /**
     * @return SaverInterface
     */
    abstract protected function getTwitterDescriptionSaver(): SaverInterface;
}
