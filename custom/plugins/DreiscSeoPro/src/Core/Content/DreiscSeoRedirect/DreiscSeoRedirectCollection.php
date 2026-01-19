<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
* @method void                add(DreiscSeoRedirectEntity $entity)
* @method void                set(string $key, DreiscSeoRedirectEntity $entity)
* @method DreiscSeoRedirectEntity[]    getIterator()
* @method DreiscSeoRedirectEntity[]    getElements()
* @method DreiscSeoRedirectEntity|null get(string $key)
* @method DreiscSeoRedirectEntity|null first()
* @method DreiscSeoRedirectEntity|null last()
*/
class DreiscSeoRedirectCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DreiscSeoRedirectEntity::class;
    }
}

