<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrContact\GpsrContactRuleDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrContact\GpsrContactSalesChannelDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrContact\GpsrContactStreamDefinition;
use Acris\Gpsr\Custom\Aggregate\GpsrContactTranslation\GpsrContactTranslationDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;

use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class GpsrContactDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'acris_gpsr_contact';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return GpsrContactCollection::class;
    }

    public function getEntityClass(): string
    {
        return GpsrContactEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new StringField('internal_id', 'internalId'))->addFlags(new ApiAware()),
            (new TranslatedField('internalName'))->addFlags(new Required()),
            (new TranslatedField('internalNotice'))->addFlags(new Required()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware()),

            (new TranslatedField('headline'))->addFlags(new Required()),
            (new TranslatedField('text'))->addFlags(new Required()),
            (new StringField('display_type', 'displayType'))->addFlags(new ApiAware()),
            (new StringField('tab_position', 'tabPosition'))->addFlags(new ApiAware()),
            (new TranslatedField('modalInfoText'))->addFlags(new Required()),
            (new TranslatedField('modalLinkText'))->addFlags(new Required()),
            (new StringField('description_display', 'descriptionDisplay'))->addFlags(new ApiAware()),
            (new StringField('description_position', 'descriptionPosition'))->addFlags(new ApiAware()),
            (new StringField('display_separator', 'displaySeparator'))->addFlags(new ApiAware()),

            (new TranslatedField('name'))->addFlags(new Required()),
            (new TranslatedField('street'))->addFlags(new ApiAware()),
            (new TranslatedField('houseNumber'))->addFlags(new ApiAware()),
            (new TranslatedField('zipcode'))->addFlags(new ApiAware()),
            (new TranslatedField('city'))->addFlags(new ApiAware()),
            (new TranslatedField('country'))->addFlags(new Required()),
            (new TranslatedField('phoneNumber'))->addFlags(new ApiAware()),
            (new TranslatedField('address'))->addFlags(new ApiAware()),

            (new ManyToManyAssociationField('productStreams', ProductStreamDefinition::class, GpsrContactStreamDefinition::class, 'gpsr_contact_id', 'product_stream_id'))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField('salesChannels', SalesChannelDefinition::class, GpsrContactSalesChannelDefinition::class, 'gpsr_contact_id', 'sales_channel_id'))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField("rules", RuleDefinition::class, GpsrContactRuleDefinition::class, "gpsr_contact_id", "rule_id"))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(GpsrContactTranslationDefinition::class, 'acris_gpsr_contact_id'))->addFlags(new ApiAware()),
        ]);
    }
}
