<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom;

use Acris\DiscountGroup\Custom\Aggregate\DiscountGroupProductStream\DiscountGroupProductStreamDefinition;
use Acris\DiscountGroup\Custom\Aggregate\DiscountGroupRule\DiscountGroupRuleDefinition;
use Acris\DiscountGroup\Custom\Aggregate\DiscountGroupTranslationDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyIdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class DiscountGroupDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'acris_discount_group';

    public const DISCOUNT_TYPE_ABSOLUTE = 'absolute';
    public const DISCOUNT_TYPE_PERCENTAGE = 'percentage';

    public const LIST_PRICE_TYPE_IGNORE = 'ignore';
    public const LIST_PRICE_TYPE_SET = 'set';
    public const LIST_PRICE_TYPE_SET_PRICE = 'setPrice';
    public const LIST_PRICE_TYPE_REMOVE = 'remove';
    public const LIST_PRICE_TYPE_RRP = 'rrp';
    public const LIST_PRICE_TYPE_SET_RRP = 'setRrp';
    public const LIST_PRICE_TYPE_SET_PURCHASE_PRICE = 'purchasePrice';

    public const CALCULATION_TYPE_SURCHARGE = 'surcharge';
    public const CALCULATION_TYPE_DISCOUNT = 'discount';

    public const CUSTOMER_ASSIGNMENT_TYPE_CUSTOMER = 'customer';
    public const CUSTOMER_ASSIGNMENT_TYPE_CUSTOMER_RULES = 'rules';
    public const CUSTOMER_ASSIGNMENT_TYPE_CUSTOMER_DISCOUNT_GROUP = 'materialGroup';
    public const CUSTOMER_ASSIGNMENT_TYPE_CUSTOMER_EVERY = 'everyCustomer';

    public const PRODUCT_ASSIGNMENT_TYPE_PRODUCT = 'product';
    public const PRODUCT_ASSIGNMENT_TYPE_MATERIAL_GROUP = 'materialGroup';
    public const PRODUCT_ASSIGNMENT_TYPE_DYNAMIC_PRODUCT_GROUP = 'dynamicProductGroup';
    public const PRODUCT_ASSIGNMENT_TYPE_EVERY_PRODUCT = 'everyProduct';

    public const CALCULATION_BASE_PRICE = 'price';
    public const CALCULATION_BASE_LIST_PRICE = 'listPrice';
    public const CALCULATION_BASE_RRP = 'rrp';
    public const CALCULATION_BASE_PURCHASE_PRICE = 'purchasePrice';

    public const RRP_TAX_AUTO = 'auto';
    public const RRP_TAX_GROSS = 'gross';
    public const RRP_TAX_NET = 'net';

    public const RRP_TAX_DISPLAY_AUTO = 'auto';
    public const RRP_TAX_DISPLAY_GROSS = 'gross';
    public const RRP_TAX_DISPLAY_NET = 'net';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DiscountGroupCollection::class;
    }

    public function getEntityClass(): string
    {
        return DiscountGroupEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new VersionField(),
            new FkField('product_id', 'productId', ProductDefinition::class),
            new ReferenceVersionField(ProductDefinition::class),
            new FkField('customer_id', 'customerId', CustomerDefinition::class),

            (new StringField('internal_name', 'internalName')),
            (new StringField('internal_id', 'internalId')),
            new BoolField('active', 'active'),
            new DateTimeField('active_from', 'activeFrom'),
            new DateTimeField('active_until', 'activeUntil'),
            new FloatField('priority', 'priority'),
            (new FloatField('discount', 'discount'))->addFlags(new Required()),
            new StringField('discount_type', 'discountType'),
            new StringField('list_price_type', 'listPriceType'),
            new StringField('calculation_base', 'calculationBase'),
            new StringField('rrp_tax', 'rrpTax'),
            new StringField('rrp_tax_display', 'rrpTaxDisplay'),
            (new StringField('customer_assignment_type', 'customerAssignmentType'))->addFlags(new Required()),
            (new StringField('product_assignment_type', 'productAssignmentType'))->addFlags(new Required()),
            new StringField('calculation_type', 'calculationType'),
            new StringField('material_group', 'materialGroup'),
            new StringField('discount_group', 'discountGroup'),
            new BoolField('excluded', 'excluded'),
            new BoolField('account_display', 'accountDisplay'),
            new IntField('min_quantity', 'minQuantity'),
            new IntField('max_quantity', 'maxQuantity'),

            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class),
            new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class),
            new ManyToManyIdField('rule_ids', 'ruleIds', 'rules'),
            new ManyToManyIdField('product_stream_ids', 'productStreamIds', 'productStreams'),

            new ManyToManyAssociationField('rules', RuleDefinition::class, DiscountGroupRuleDefinition::class, 'discount_group_id', 'rule_id'),
            new ManyToManyAssociationField('productStreams', ProductStreamDefinition::class, DiscountGroupProductStreamDefinition::class, 'discount_group_id', 'product_stream_id'),
            new CustomFields(),
            (new TranslatedField('displayText'))->addFlags(new ApiAware()),
            (new TranslatedField('displayName'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(DiscountGroupTranslationDefinition::class, 'acris_discount_group_id'))->addFlags(new ApiAware()),

        ]);
    }
}
