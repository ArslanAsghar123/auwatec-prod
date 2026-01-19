<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components\Cache;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DiscountGroupCacheService
{
    public const DISCOUNT_GROUP_DATE_RANGE_CACHE_KEY = 'acrisDiscountGroupDateRangeCacheKey';
    public const DISCOUNT_GROUP_DATE_RANGE_CACHE_TAG = 'acrisDiscountGroupDateRangeCacheTag';

    public function __construct(
        private readonly Connection $connection,
        private readonly TagAwareAdapterInterface $cache
    ) { }

    public function getActiveDiscountGroupsWithDateRangeHash(): ?string
    {
        $value = $this->cache->get(self::DISCOUNT_GROUP_DATE_RANGE_CACHE_KEY, function (ItemInterface $item) {
            $discountGroupResult = $this->connection->fetchAllAssociative('SELECT adg.id, adg.active_from, adg.active_until  FROM acris_discount_group adg WHERE adg.active = 1 AND adg.active_from IS NOT NULL OR adg.active_until IS NOT NULL LIMIT 500;');
            $item->tag([self::DISCOUNT_GROUP_DATE_RANGE_CACHE_TAG]);
            return CacheValueCompressor::compress($discountGroupResult);
        });

        $discountGroupResult = CacheValueCompressor::uncompress($value);

        if(empty($discountGroupResult)) return null;

        $today = new \DateTime();
        $today = $today->setTimezone(new \DateTimeZone('UTC'));
        $collectedIds = [];
        foreach ($discountGroupResult as $discountGroup) {
            if(!empty($discountGroup['active_from'])) {
                $activeFrom = new \DateTime($discountGroup['active_from']);
                if($today >= $activeFrom) {
                    $collectedIds[] = Uuid::fromBytesToHex($discountGroup['id']);
                }
            }
            if(!empty($discountGroup['active_until'])) {
                $activeUntil = new \DateTime($discountGroup['active_until']);
                if($today <= $activeUntil) {
                    $collectedIds[] = Uuid::fromBytesToHex($discountGroup['id']);
                }
            }
        }

        if(!empty($collectedIds)) {
            return md5(implode(',', $collectedIds));
        }
        return null;
    }
}
