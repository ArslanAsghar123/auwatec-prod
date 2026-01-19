<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;

class DreiscSeoRedirectDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'dreisc_seo_redirect';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DreiscSeoRedirectCollection::class;
    }

    public function getEntityClass(): string
    {
        return DreiscSeoRedirectEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new Required(), new PrimaryKey()),
            new BoolField('active', 'active'),
            (new StringField('redirect_http_status_code', 'redirectHttpStatusCode'))
                ->addFlags(
                    new SearchRanking(SearchRanking::LOW_SEARCH_RANKING)
                ),

            new StringField('source_type', 'sourceType'),
            new BoolField('has_source_sales_channel_domain_restriction', 'hasSourceSalesChannelDomainRestriction'),
            new ListField('source_sales_channel_domain_restriction_ids', 'sourceSalesChannelDomainRestrictionIds'),
            new FkField(
                'source_sales_channel_domain_id',
                'sourceSalesChannelDomainId',
                SalesChannelDomainDefinition::class
            ),
            (new LongTextField('source_path', 'sourcePath'))
                ->addFlags(
                    new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)
                ),
            new FkField(
                'source_product_id',
                'sourceProductId',
                ProductDefinition::class
            ),
            (new ReferenceVersionField(ProductDefinition::class, 'source_product_version_id'))->addFlags(new PrimaryKey(), new Required()),
            new FkField(
                'source_category_id',
                'sourceCategoryId',
                CategoryDefinition::class
            ),
            (new ReferenceVersionField(CategoryDefinition::class, 'source_category_version_id'))->addFlags(new PrimaryKey(), new Required()),

            new StringField('redirect_type', 'redirectType'),
            (new LongTextField('redirect_url', 'redirectUrl'))
                ->addFlags(new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            new FkField(
                'redirect_sales_channel_domain_id',
                'redirectSalesChannelDomainId',
                SalesChannelDomainDefinition::class
            ),
            (new LongTextField('redirect_path', 'redirectPath'))
                ->addFlags(
                    new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)
                ),
            new FkField(
                'redirect_product_id',
                'redirectProductId',
                ProductDefinition::class
            ),
            (new ReferenceVersionField(ProductDefinition::class, 'redirect_product_version_id'))->addFlags(new PrimaryKey(), new Required()),
            new FkField(
                'redirect_category_id',
                'redirectCategoryId',
                CategoryDefinition::class
            ),
            (new ReferenceVersionField(CategoryDefinition::class, 'redirect_category_version_id'))->addFlags(new PrimaryKey(), new Required()),

            new BoolField('has_deviating_redirect_sales_channel_domain', 'hasDeviatingRedirectSalesChannelDomain'),
            new FkField(
                'deviating_redirect_sales_channel_domain_id',
                'deviatingRedirectSalesChannelDomainId',
                SalesChannelDomainDefinition::class
            ),

            new BoolField('parameter_forwarding', 'parameterForwarding'),

            (new ManyToOneAssociationField(
                'sourceSalesChannelDomain',
                'source_sales_channel_domain_id',
                SalesChannelDomainDefinition::class
            ))->addFlags(
                new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING),
                new CascadeDelete()
            ),
            (new ManyToOneAssociationField(
                'sourceProduct',
                'source_product_id',
                ProductDefinition::class
            ))->addFlags(
                new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING),
                new CascadeDelete()
            ),
            (new ManyToOneAssociationField(
                'sourceCategory',
                'source_category_id',
                CategoryDefinition::class
            ))->addFlags(
                new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING),
                new CascadeDelete()
            ),
            (new ManyToOneAssociationField(
                'redirectSalesChannelDomain',
                'redirect_sales_channel_domain_id',
                SalesChannelDomainDefinition::class
            ))->addFlags(
                new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING),
                new CascadeDelete()
            ),
            (new ManyToOneAssociationField(
                'deviatingRedirectSalesChannelDomain',
                'deviating_redirect_sales_channel_domain_id',
                SalesChannelDomainDefinition::class
            ))->addFlags(
                new CascadeDelete()
            ),
            (new ManyToOneAssociationField(
                'redirectProduct',
                'redirect_product_id',
                ProductDefinition::class
            ))->addFlags(
                new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING),
                new CascadeDelete()
            ),
            (new ManyToOneAssociationField(
                'redirectCategory',
                'redirect_category_id',
                CategoryDefinition::class
            ))->addFlags(
                new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING),
                new CascadeDelete()
            )
        ]);
    }
}
