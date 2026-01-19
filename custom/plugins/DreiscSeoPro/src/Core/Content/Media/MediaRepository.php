<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Media;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;

/**
* @method MediaEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method MediaSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class MediaRepository extends EntityRepository
{
}

