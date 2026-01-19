<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\LandingPage;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;

/**
* @method LandingPageEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method LandingPageSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class LandingPageRepository extends EntityRepository
{
}

