<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Country;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Country\CountryEntity;

/**
* @method CountryEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method CountrySearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class CountryRepository extends EntityRepository
{
}

