<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components\Event;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class DiscountGroupGatewayFilterParameterEvent extends Event
{
    private array $customerIds;
    private array $discountGroupValues;
    private SalesChannelContext $salesChannelContext;

    public function __construct(array $customerIds, array $discountGroupValues, SalesChannelContext $salesChannelContext)
    {
        $this->customerIds = $customerIds;
        $this->discountGroupValues = $discountGroupValues;
        $this->salesChannelContext = $salesChannelContext;
    }

    /**
     * @return array
     */
    public function getCustomerIds(): array
    {
        return $this->customerIds;
    }

    /**
     * @param array $customerIds
     */
    public function setCustomerIds(array $customerIds): void
    {
        $this->customerIds = $customerIds;
    }

    /**
     * @return array
     */
    public function getDiscountGroupValues(): array
    {
        return $this->discountGroupValues;
    }

    /**
     * @param array $discountGroupValues
     */
    public function setDiscountGroupValues(array $discountGroupValues): void
    {
        $this->discountGroupValues = $discountGroupValues;
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
    }
}
