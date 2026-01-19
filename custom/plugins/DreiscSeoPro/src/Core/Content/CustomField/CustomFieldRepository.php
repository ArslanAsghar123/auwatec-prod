<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\CustomField;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;

/**
* @method CustomFieldEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method CustomFieldSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class CustomFieldRepository extends EntityRepository
{
}

