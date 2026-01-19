<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\SystemConfig;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;

/**
* @method SystemConfigEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method SystemConfigSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class SystemConfigRepository extends EntityRepository
{
}

