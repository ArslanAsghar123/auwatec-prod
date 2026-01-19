<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\SalesChannel\Aggregate\SalesChannelDomain;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

/**
* @method SalesChannelDomainEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method SalesChannelDomainSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class SalesChannelDomainRepository extends EntityRepository
{
}

