<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Product;

use DreiscSeoPro\Core\Foundation\Dal\EntityRepository;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Context;

/**
 * @method ProductEntity    get(string $id, array $associations = null, ?Context $context = null, $disableCache = false)
* @method ProductSearchResult    search(Criteria $criteria, Context $context = null, $disableCache = false)
*/
class ProductRepository extends EntityRepository
{

}

