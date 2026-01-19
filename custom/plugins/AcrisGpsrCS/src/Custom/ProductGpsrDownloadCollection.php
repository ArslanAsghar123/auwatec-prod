<?php

namespace Acris\Gpsr\Custom;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ProductGpsrDownloadCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductGpsrDownloadEntity::class;
    }
}