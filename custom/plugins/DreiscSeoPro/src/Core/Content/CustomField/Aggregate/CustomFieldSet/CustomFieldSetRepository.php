<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\CustomField\Aggregate\CustomFieldSet;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;

/**
* @method CustomFieldSetEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method CustomFieldSetSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class CustomFieldSetRepository extends EntityRepository
{
}

