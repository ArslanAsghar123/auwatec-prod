<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Storefront\Subscriber;

use Acris\DiscountGroup\Components\Cache\DiscountGroupCacheService;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiscountGroupCacheInvalidationSubscriber implements EventSubscriberInterface
{
    const DISCOUNT_GROUP_WRITTEN_EVENT = 'acris_discount_group.written';

    public const PRODUCT_CACHE_TAG = 'config.core.listing.hideCloseoutProductsWhenOutOfStock';

    public function __construct(
        private readonly CacheInvalidator $invalidator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            self::DISCOUNT_GROUP_WRITTEN_EVENT => 'onDiscountGroupWritten'
        ];
    }

    public function onDiscountGroupWritten(EntityWrittenEvent $event)
    {
        $this->invalidator->invalidate([self::PRODUCT_CACHE_TAG, DiscountGroupCacheService::DISCOUNT_GROUP_DATE_RANGE_CACHE_TAG]);
    }
}
