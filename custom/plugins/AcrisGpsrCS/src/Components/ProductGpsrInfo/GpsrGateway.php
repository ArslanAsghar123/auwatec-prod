<?php declare(strict_types=1);

namespace Acris\Gpsr\Components\ProductGpsrInfo;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class GpsrGateway
{
    public function __construct(private readonly EntityRepository $gpsrManufacturerRepository, private readonly EntityRepository $gpsrContactRepository, private readonly EntityRepository $gpsrNoteRepository)
    {
    }

    public function getGpsrManufacturersInfoFromDB(array $streamIds, SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        $criteria = $this->getCriteria($streamIds, $salesChannelContext);
        $criteria->addAssociation("acrisGpsrManufacturerDownloads");
        return $this->gpsrManufacturerRepository->search($criteria, $salesChannelContext->getContext());
    }

    public function getGpsrContactsInfoFromDB(array $streamIds, SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        $criteria = $this->getCriteria($streamIds, $salesChannelContext);
        $criteria->addAssociation("acrisGpsrContactDownloads");

        return $this->gpsrContactRepository->search($criteria, $salesChannelContext->getContext());
    }

    public function getGpsrNotesInfoFromDB(array $streamIds, SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        $criteria = $this->getCriteria($streamIds, $salesChannelContext);
        $criteria->addAssociation("acrisGpsrNoteDownloads");

        $criteria->addAssociation('media');

        return $this->gpsrNoteRepository->search($criteria, $salesChannelContext->getContext());
    }

    private function getCriteria(array $streamIds, SalesChannelContext $salesChannelContext): Criteria
    {
        $criteria = new Criteria();
        $filter = [];

        $criteria
            ->addAssociation('productStreams')
            ->addAssociation('salesChannels')
            ->addAssociation('rules');

        $criteria->addSorting(new FieldSorting('priority',FieldSorting::DESCENDING, true));

        $filter[] = new EqualsFilter('active', true);
        $filter[] = new EqualsAnyFilter('productStreams.id', $streamIds);
        $filter[] = new OrFilter([new EqualsFilter('salesChannels.id', null), new EqualsFilter('salesChannels.id', $salesChannelContext->getSalesChannelId())]);
        if (!empty($salesChannelContext->getRuleIds())) {
            $filter[] = new OrFilter([new EqualsFilter('rules.id', null), new EqualsAnyFilter('rules.id', $salesChannelContext->getRuleIds())]);
        }
        $criteria->addFilter(new AndFilter($filter));


        return $criteria;
    }
}
