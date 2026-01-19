<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoBulk\Aggregate\DreiscSeoBulkTemplate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
* @method void                add(DreiscSeoBulkTemplateEntity $entity)
* @method void                set(string $key, DreiscSeoBulkTemplateEntity $entity)
* @method DreiscSeoBulkTemplateEntity[]    getIterator()
* @method DreiscSeoBulkTemplateEntity[]    getElements()
* @method DreiscSeoBulkTemplateEntity|null get(string $key)
* @method DreiscSeoBulkTemplateEntity|null first()
* @method DreiscSeoBulkTemplateEntity|null last()
*/
class DreiscSeoBulkTemplateCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DreiscSeoBulkTemplateEntity::class;
    }
}

