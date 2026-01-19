<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrContactTranslation\GpsrContactTranslationCollection;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;

class GpsrContactEntity extends AbstractGpsrModuleEntity
{
    use EntityIdTrait;
    protected $parentId;

}
