<?php
declare(strict_types=1);

namespace Acris\DiscountGroup\Core\Framework\DataAbstractionLayer\Cache;

use Acris\DiscountGroup\Components\Cache\DiscountGroupCacheService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class EntityCacheKeyGenerator extends \Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator
{
    public function __construct(
        private readonly \Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator $parent,
        private readonly DiscountGroupCacheService $discountGroupCacheService
    ) { }

    public function getSalesChannelContextHash(SalesChannelContext $context, array $areas = []): string
    {
        $hash = $this->parent->getSalesChannelContextHash($context, $areas);

        $parts = [
            $hash,
            $context->getCustomer() !== null
        ];

        if($context->getCustomer() instanceof CustomerEntity) {
            $customFields = $context->getCustomer()->getCustomFields();
            $discountGroupValue = !empty($customFields) && is_array($customFields) && array_key_exists('acris_discount_group_customer_value', $customFields) && !empty($customFields['acris_discount_group_customer_value']) && is_string($customFields['acris_discount_group_customer_value']) ? md5($customFields['acris_discount_group_customer_value']) : null;
            if(!empty($discountGroupValue)) {
                $parts[] = $discountGroupValue;
            }
        }

        $discountGroupHash = $this->discountGroupCacheService->getActiveDiscountGroupsWithDateRangeHash();
        if(!empty($discountGroupHash)) {
            $parts[] = $discountGroupHash;
        }

        return md5(json_encode($parts));
    }
}
