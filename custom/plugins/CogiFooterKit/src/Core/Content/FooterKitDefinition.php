<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Core\Content;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cogi\CogiFooterKit\Core\Content\Aggregate\FooterKitTranslation\FooterKitTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class FooterKitDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'cogi_footer_kit';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return FooterKitCollection::class;
    }

    public function getEntityClass(): string
    {
        return FooterKitEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class),

            new StringField('name', 'name'),
            new JsonField('navigation_config', 'navigationConfig'),
            new JsonField('information_config', 'informationConfig'),
            new JsonField('payment_shipping_config', 'paymentShippingConfig'),
            new JsonField('bottom_config', 'bottomConfig'),

            new TranslatedField('navigationBlock'),
            new TranslatedField('informationBlock'),
            new TranslatedField('customLink'),
            new TranslatedField('socialMediaString'),
            new TranslatedField('paymentString'),
            new TranslatedField('shippingString'),
            new TranslatedField('productSliderTitle'),

            new OneToOneAssociationField('salesChannel', 'sales_channel_id', 'id', SalesChannelDefinition::class, false),

            (new TranslationsAssociationField(FooterKitTranslationDefinition::class, 'cogi_footer_kit_id'))
            ->addFlags(new Required()),
        ]);
    }
}