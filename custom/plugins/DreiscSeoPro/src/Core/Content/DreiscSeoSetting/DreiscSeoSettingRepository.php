<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoSetting;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;

/**
* @method DreiscSeoSettingEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method DreiscSeoSettingSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class DreiscSeoSettingRepository extends EntityRepository
{
}

