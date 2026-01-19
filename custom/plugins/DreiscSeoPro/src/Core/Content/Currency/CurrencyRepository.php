<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Currency;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;

/**
* @method CurrencyEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method CurrencySearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class CurrencyRepository extends EntityRepository
{
}

