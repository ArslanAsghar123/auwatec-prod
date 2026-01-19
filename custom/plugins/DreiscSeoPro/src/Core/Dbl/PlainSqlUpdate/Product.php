<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Dbl\PlainSqlUpdate;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Content\Product\ProductRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\Uuid\Uuid;

class Product
{
    public function __construct(private readonly Common $commonPlainSqlUpdater)
    {
    }

    public function update(array $updates)
    {
        $this->commonPlainSqlUpdater->updateTranslations(
            'product',
            $updates
        );
    }
}
