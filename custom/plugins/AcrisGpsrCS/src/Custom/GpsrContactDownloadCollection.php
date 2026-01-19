<?php

namespace Acris\Gpsr\Custom;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class GpsrContactDownloadCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return GpsrContactDownloadEntity::class;
    }
}