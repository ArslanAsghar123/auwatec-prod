<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Storefront\Subscriber;

use Acris\DiscountGroup\Components\Cache\DiscountGroupCacheService;
use Shopware\Core\Framework\Adapter\Cache\Event\HttpCacheKeyEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiscountGroupCacheSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly DiscountGroupCacheService $discountGroupCacheService
    ) { }

    public static function getSubscribedEvents(): array
    {
        return [
            HttpCacheKeyEvent::class => 'onHttpCacheKey'
        ];
    }

    public function onHttpCacheKey(HttpCacheKeyEvent $event): void
    {
        $discountGroupHash = $this->discountGroupCacheService->getActiveDiscountGroupsWithDateRangeHash();
        if(!empty($discountGroupHash)) {
            $event->add($discountGroupHash, $discountGroupHash);
        }
    }
}
