<?php declare(strict_types=1);
namespace phpSchmied\LastSeenProducts\Core\CrossSelling;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Content\Product\ProductDefinition;

class CustomerLastSeenDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'php_schmiede_cross_selling_customer_last_seen';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return CustomerLastSeenEntity::class;
    }

    public function getCollectionClass(): string
    {
        return CustomerLastSeenCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new DateTimeField('last_view', 'lastView'))->addFlags(new Required()),
        ]);
    }
}