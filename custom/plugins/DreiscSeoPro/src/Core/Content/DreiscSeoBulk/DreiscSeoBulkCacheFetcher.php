<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;

class DreiscSeoBulkCacheFetcher
{
    public function __construct(private readonly DreiscSeoBulkRepository $dreiscSeoBulkRepository)
    {
    }

    /**
     * Fetches the seo bulk configuration for the given setting
     *
     * @param string|null $salesChannelId
     */
    public function fetch(string $categoryId, string $area, string $seoOption, string $languageId, string $salesChannelId = null, ?bool $inherit = null): ?EntitySearchResult
    {
        $filter = [
            new EqualsFilter(DreiscSeoBulkEntity::CATEGORY_ID__PROPERTY_NAME, $categoryId),
            new EqualsFilter(DreiscSeoBulkEntity::AREA__PROPERTY_NAME, $area),
            new EqualsFilter(DreiscSeoBulkEntity::SEO_OPTION__PROPERTY_NAME, $seoOption),
            new EqualsFilter(DreiscSeoBulkEntity::LANGUAGE_ID__PROPERTY_NAME, $languageId),
            new EqualsFilter(DreiscSeoBulkEntity::SALES_CHANNEL_ID__PROPERTY_NAME, $salesChannelId)
        ];

        if (is_bool($inherit)) {
            $filter[] = new EqualsFilter(DreiscSeoBulkEntity::INHERIT__PROPERTY_NAME, $inherit);
        }

        return $this->dreiscSeoBulkRepository->search(
            (new Criteria())
                ->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, $filter))

                ->addAssociation(DreiscSeoBulkEntity::DREISC_SEO_BULK_TEMPLATE__PROPERTY_NAME)
                ->addAssociation(DreiscSeoBulkEntity::CATEGORY__PROPERTY_NAME)
        );


//        $cachedSeoBulkEntitySearchResult = $this->getAllSeoBulksAsEntitySearchResult();
//        if (null === $cachedSeoBulkEntitySearchResult) {
//            return null;
//        }
//
//        $filteredEntitySearchResult = $cachedSeoBulkEntitySearchResult
//            ->filterByProperty(DreiscSeoBulkEntity::CATEGORY_ID__PROPERTY_NAME, $categoryId)
//            ->filterByProperty(DreiscSeoBulkEntity::AREA__PROPERTY_NAME, $area)
//            ->filterByProperty(DreiscSeoBulkEntity::SEO_OPTION__PROPERTY_NAME, $seoOption)
//            ->filterByProperty(DreiscSeoBulkEntity::LANGUAGE_ID__PROPERTY_NAME, $languageId)
//            ->filterByProperty(DreiscSeoBulkEntity::SALES_CHANNEL_ID__PROPERTY_NAME, $salesChannelId);
//
//        if (is_bool($inherit)) {
//            $filteredEntitySearchResult->filterByProperty(DreiscSeoBulkEntity::INHERIT__PROPERTY_NAME, $inherit);
//        }
//
//        return $filteredEntitySearchResult;
    }
}
