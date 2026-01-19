<?php declare(strict_types=1);

namespace Acris\Gpsr\Components\ProductGpsrInfo\Struct;

use Shopware\Core\Framework\Struct\Collection;

/**
 * @method void       set(string $key, GpsrInfoCollection $entity)
 * @method GpsrInfoCollection[]    getIterator()
 * @method GpsrInfoCollection[]    getElements()
 * @method GpsrInfoCollection|null get(string $key)
 * @method GpsrInfoCollection|null first()
 * @method GpsrInfoCollection|null last()
 */
class GpsrInfoGroupedCollection extends Collection
{
    protected bool $isGrouped = true;

    public function isGrouped(): bool
    {
        return $this->isGrouped;
    }

    public function setIsGrouped(bool $isGrouped): void
    {
        $this->isGrouped = $isGrouped;
    }
}
