<?php

namespace Acris\Gpsr\Custom;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class GpsrManufacturerDownloadCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrManufacturerDownloadEntity::class;
    }
}