<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Core\Content\Aggregate\FooterKitTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Cogi\CogiFooterKit\Core\Content\FooterKitDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;

class FooterKitTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'cogi_footer_kit_translation';
    }

    public function getCollectionClass(): string
    {
        return FooterKitTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return FooterKitTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return FooterKitDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            new JsonField('navigation_block', 'navigationBlock'),
            new JsonField('information_block', 'informationBlock'),
            new JsonField('custom_link', 'customLink'),
            new StringField('social_media_string', 'socialMediaString'),
            new StringField('payment_string', 'paymentString'),
            new StringField('shipping_string', 'shippingString'),
            new StringField('product_slider_title', 'productSliderTitle'),
        ]);
    }
}