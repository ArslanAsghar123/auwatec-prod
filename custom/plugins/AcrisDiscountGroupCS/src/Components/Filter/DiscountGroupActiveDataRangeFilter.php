<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components\Filter;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

class DiscountGroupActiveDataRangeFilter extends MultiFilter
{
    public function __construct()
    {
        $today = new \DateTime();
        $today = $today->setTimezone(new \DateTimeZone('UTC'));

        $todayStart = $today->format('Y-m-d H:i:s');
        $todayEnd = $today->format('Y-m-d H:i:s');

        $filterNoDateRange = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('activeFrom', null),
                new EqualsFilter('activeUntil', null),
            ]
        );

        $filterStartedNoEndDate = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new RangeFilter('activeFrom', ['lte' => $todayStart]),
                new EqualsFilter('activeUntil', null),
            ]
        );

        $filterActiveNoStartDate = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('activeFrom', null),
                new RangeFilter('activeUntil', ['gt' => $todayEnd]),
            ]
        );

        $activeDateRangeFilter = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new RangeFilter('activeFrom', ['lte' => $todayStart]),
                new RangeFilter('activeUntil', ['gt' => $todayEnd]),
            ]
        );

        parent::__construct(
            MultiFilter::CONNECTION_OR,
            [
                $filterNoDateRange,
                $filterActiveNoStartDate,
                $filterStartedNoEndDate,
                $activeDateRangeFilter,
            ]
        );
    }
}
