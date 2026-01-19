<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoSetting;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
* @method void                add(DreiscSeoSettingEntity $entity)
* @method void                set(string $key, DreiscSeoSettingEntity $entity)
* @method DreiscSeoSettingEntity[]    getIterator()
* @method DreiscSeoSettingEntity[]    getElements()
* @method DreiscSeoSettingEntity|null get(string $key)
* @method DreiscSeoSettingEntity|null first()
* @method DreiscSeoSettingEntity|null last()
*/
class DreiscSeoSettingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return DreiscSeoSettingEntity::class;
    }
}

