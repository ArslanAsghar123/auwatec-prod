<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components;

use Acris\DiscountGroup\Components\Event\DiscountGroupGatewayFilterParameterEvent;
use Acris\DiscountGroup\Components\Filter\DiscountGroupActiveDataRangeFilter;
use Acris\DiscountGroup\Custom\DiscountGroupDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DiscountGroupGateway
{
    private array $discountGroupSearchResult;

    public function __construct(
        private readonly EntityRepository $discountGroupRepository,
        private readonly EntityRepository $productStreamMappingRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $this->discountGroupSearchResult = [];
    }

    public function getAllDiscountGroupsForProduct(SalesChannelContext $salesChannelContext, string $productId = null, ?array $productStreamIds = null): EntitySearchResult
    {
        if(!empty($this->discountGroupSearchResult) && array_key_exists($productId, $this->discountGroupSearchResult)
            && !empty($this->discountGroupSearchResult[$productId]) && $this->discountGroupSearchResult[$productId] instanceof EntitySearchResult) {
            return $this->discountGroupSearchResult[$productId];
        }

        $criteria = (new Criteria());
        $criteria = $this->addCriteria($productStreamIds, $productId, $criteria, $salesChannelContext);

        $this->discountGroupSearchResult[$productId] = $this->discountGroupRepository->search($criteria, $salesChannelContext->getContext());
        return $this->discountGroupSearchResult[$productId];
    }

    public function getAllDiscountGroups(SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        $criteria = (new Criteria());
        $criteria = $this->addCriteria(null, null, $criteria, $salesChannelContext, true);

        return $this->discountGroupRepository->search($criteria, $salesChannelContext->getContext());
    }

    private function addCriteria(?array $productStreamIds, ?string $productId, Criteria $criteria, SalesChannelContext $salesChannelContext, $ignoreProductFilters = false): Criteria
    {
        $customer = $salesChannelContext->getCustomer();
        $customerIds = !empty($customer) ? [$customer->getId()] : [];
        $discountGroupValues = [];

        if(!empty($customer) && !empty($customer->getCustomFields())) {
            if(array_key_exists('acris_discount_group_customer_value', $customer->getCustomFields()) && !empty($customer->getCustomFields()['acris_discount_group_customer_value'])) {
                $discountGroupValues = [$customer->getCustomFields()['acris_discount_group_customer_value']];
            } elseif(array_key_exists('acris_discount_group_value', $customer->getCustomFields()) && !empty($customer->getCustomFields()['acris_discount_group_value'])) {
                $discountGroupValues = [$customer->getCustomFields()['acris_discount_group_value']];
            }
        }

        $event = new DiscountGroupGatewayFilterParameterEvent($customerIds, $discountGroupValues, $salesChannelContext);
        $this->eventDispatcher->dispatch($event);

        $customerFilters = [
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('customerAssignmentType', DiscountGroupDefinition::CUSTOMER_ASSIGNMENT_TYPE_CUSTOMER_RULES),
                new EqualsAnyFilter('rules.id', $salesChannelContext->getRuleIds()),
            ])
        ];

        array_unshift($customerFilters, new EqualsFilter('customerAssignmentType', DiscountGroupDefinition::CUSTOMER_ASSIGNMENT_TYPE_CUSTOMER_EVERY));

        if(!empty($event->getDiscountGroupValues())) {
            array_unshift($customerFilters, new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('customerAssignmentType', DiscountGroupDefinition::CUSTOMER_ASSIGNMENT_TYPE_CUSTOMER_DISCOUNT_GROUP),
                new EqualsAnyFilter('discountGroup', $event->getDiscountGroupValues()),
            ]));
        }

        if(!empty($event->getCustomerIds())) {
            array_unshift($customerFilters, new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('customerAssignmentType', DiscountGroupDefinition::CUSTOMER_ASSIGNMENT_TYPE_CUSTOMER),
                new EqualsAnyFilter('customerId', $event->getCustomerIds())
            ]));
        }

        $productFilters = [
            new EqualsFilter('productAssignmentType', DiscountGroupDefinition::PRODUCT_ASSIGNMENT_TYPE_EVERY_PRODUCT),
            // material group will be filtered later
            new EqualsFilter('productAssignmentType', DiscountGroupDefinition::PRODUCT_ASSIGNMENT_TYPE_MATERIAL_GROUP)
        ];

        if (is_array($productStreamIds)) {
            array_unshift($productFilters, new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('productAssignmentType', DiscountGroupDefinition::PRODUCT_ASSIGNMENT_TYPE_DYNAMIC_PRODUCT_GROUP),
                new EqualsAnyFilter('productStreams.id', $productStreamIds),
                new NotFilter(NotFilter::CONNECTION_AND, [
                    new EqualsFilter('productStreams.id', null)
                ])
            ]));
        }

        if (!empty($productId)) {
            array_unshift($productFilters, new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('productAssignmentType', DiscountGroupDefinition::PRODUCT_ASSIGNMENT_TYPE_PRODUCT),
                new EqualsFilter('productId', $productId)
            ]));
        }

        $activeFilter = new DiscountGroupActiveDataRangeFilter();

        $andFilters = [
            new EqualsFilter('active', true),
            $activeFilter,
            new MultiFilter(MultiFilter::CONNECTION_OR, $customerFilters)
        ];

        if($ignoreProductFilters !== true) {
            $andFilters[] = new MultiFilter(MultiFilter::CONNECTION_OR, $productFilters);
            $criteria->addAssociation('productStreams');
        }

        $criteria->addAssociation('rules')
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, $andFilters))
            ->addSorting(new FieldSorting('priority', FieldSorting::DESCENDING));

        return $criteria;
    }

    public function getProductStreamIds(array $productIds, Context $context): array
    {
        $productStreamIds = [];

        $idSearchResult = $this->productStreamMappingRepository->searchIds((new Criteria())->addFilter(new EqualsAnyFilter('productId', $productIds)), $context);

        if ($idSearchResult->getTotal() > 0) {
            foreach ($idSearchResult->getIds() as $ids) {
                if (!empty($ids) && is_array($ids) && array_key_exists('productStreamId', $ids) && !empty($ids['productStreamId'])) {
                    $productStreamIds[] = $ids['productStreamId'];
                }
            }
        }

        return $productStreamIds;
    }
}
