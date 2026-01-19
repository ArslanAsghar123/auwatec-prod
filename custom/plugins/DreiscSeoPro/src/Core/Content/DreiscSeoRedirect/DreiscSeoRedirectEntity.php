<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Category\CategoryEntity;

class DreiscSeoRedirectEntity extends Entity
{
    use EntityIdTrait;

    final const ID__STORAGE_NAME = 'id';
    final const ID__PROPERTY_NAME = 'id';
    final const ACTIVE__STORAGE_NAME = 'active';
    final const ACTIVE__PROPERTY_NAME = 'active';
    final const REDIRECT_HTTP_STATUS_CODE__STORAGE_NAME = 'redirect_http_status_code';
    final const REDIRECT_HTTP_STATUS_CODE__PROPERTY_NAME = 'redirectHttpStatusCode';
    final const SOURCE_TYPE__STORAGE_NAME = 'source_type';
    final const SOURCE_TYPE__PROPERTY_NAME = 'sourceType';
    final const HAS_SOURCE_SALES_CHANNEL_DOMAIN_RESTRICTION__STORAGE_NAME = 'has_source_sales_channel_domain_restriction';
    final const HAS_SOURCE_SALES_CHANNEL_DOMAIN_RESTRICTION__PROPERTY_NAME = 'hasSourceSalesChannelDomainRestriction';
    final const SOURCE_SALES_CHANNEL_DOMAIN_RESTRICTION_IDS__STORAGE_NAME = 'source_sales_channel_domain_restriction_ids';
    final const SOURCE_SALES_CHANNEL_DOMAIN_RESTRICTION_IDS__PROPERTY_NAME = 'sourceSalesChannelDomainRestrictionIds';
    final const SOURCE_SALES_CHANNEL_DOMAIN_ID__STORAGE_NAME = 'source_sales_channel_domain_id';
    final const SOURCE_SALES_CHANNEL_DOMAIN_ID__PROPERTY_NAME = 'sourceSalesChannelDomainId';
    final const SOURCE_PATH__STORAGE_NAME = 'source_path';
    final const SOURCE_PATH__PROPERTY_NAME = 'sourcePath';
    final const SOURCE_PRODUCT_ID__STORAGE_NAME = 'source_product_id';
    final const SOURCE_PRODUCT_ID__PROPERTY_NAME = 'sourceProductId';
    final const SOURCE_CATEGORY_ID__STORAGE_NAME = 'source_category_id';
    final const SOURCE_CATEGORY_ID__PROPERTY_NAME = 'sourceCategoryId';
    final const REDIRECT_TYPE__STORAGE_NAME = 'redirect_type';
    final const REDIRECT_TYPE__PROPERTY_NAME = 'redirectType';
    final const REDIRECT_URL__STORAGE_NAME = 'redirect_url';
    final const REDIRECT_URL__PROPERTY_NAME = 'redirectUrl';
    final const REDIRECT_SALES_CHANNEL_DOMAIN_ID__STORAGE_NAME = 'redirect_sales_channel_domain_id';
    final const REDIRECT_SALES_CHANNEL_DOMAIN_ID__PROPERTY_NAME = 'redirectSalesChannelDomainId';
    final const REDIRECT_PATH__STORAGE_NAME = 'redirect_path';
    final const REDIRECT_PATH__PROPERTY_NAME = 'redirectPath';
    final const REDIRECT_PRODUCT_ID__STORAGE_NAME = 'redirect_product_id';
    final const REDIRECT_PRODUCT_ID__PROPERTY_NAME = 'redirectProductId';
    final const REDIRECT_CATEGORY_ID__STORAGE_NAME = 'redirect_category_id';
    final const REDIRECT_CATEGORY_ID__PROPERTY_NAME = 'redirectCategoryId';
    final const HAS_DEVIATING_REDIRECT_SALES_CHANNEL_DOMAIN__STORAGE_NAME = 'has_deviating_redirect_sales_channel_domain';
    final const HAS_DEVIATING_REDIRECT_SALES_CHANNEL_DOMAIN__PROPERTY_NAME = 'hasDeviatingRedirectSalesChannelDomain';
    final const DEVIATING_REDIRECT_SALES_CHANNEL_DOMAIN_ID__STORAGE_NAME = 'deviating_redirect_sales_channel_domain_id';
    final const DEVIATING_REDIRECT_SALES_CHANNEL_DOMAIN_ID__PROPERTY_NAME = 'deviatingRedirectSalesChannelDomainId';
    final const SOURCE_SALES_CHANNEL_DOMAIN__STORAGE_NAME = 'source_sales_channel_domain';
    final const SOURCE_SALES_CHANNEL_DOMAIN__PROPERTY_NAME = 'sourceSalesChannelDomain';
    final const SOURCE_PRODUCT__STORAGE_NAME = 'source_product';
    final const SOURCE_PRODUCT__PROPERTY_NAME = 'sourceProduct';
    final const SOURCE_CATEGORY__STORAGE_NAME = 'source_category';
    final const SOURCE_CATEGORY__PROPERTY_NAME = 'sourceCategory';
    final const REDIRECT_SALES_CHANNEL_DOMAIN__STORAGE_NAME = 'redirect_sales_channel_domain';
    final const REDIRECT_SALES_CHANNEL_DOMAIN__PROPERTY_NAME = 'redirectSalesChannelDomain';
    final const DEVIATING_REDIRECT_SALES_CHANNEL_DOMAIN__STORAGE_NAME = 'deviating_redirect_sales_channel_domain';
    final const DEVIATING_REDIRECT_SALES_CHANNEL_DOMAIN__PROPERTY_NAME = 'deviatingRedirectSalesChannelDomain';
    final const REDIRECT_PRODUCT__STORAGE_NAME = 'redirect_product';
    final const REDIRECT_PRODUCT__PROPERTY_NAME = 'redirectProduct';
    final const REDIRECT_CATEGORY__STORAGE_NAME = 'redirect_category';
    final const REDIRECT_CATEGORY__PROPERTY_NAME = 'redirectCategory';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool|null
     */
    protected $active;

    /**
     * @var string|null
     */
    protected $redirectHttpStatusCode;

    /**
     * @var string|null
     */
    protected $sourceType;

    /**
     * @var bool|null
     */
    protected $hasSourceSalesChannelDomainRestriction;

    /**
     * @var array|null
     */
    protected $sourceSalesChannelDomainRestrictionIds;

    /**
     * @var string|null
     */
    protected $sourceSalesChannelDomainId;

    /**
     * @var string|null
     */
    protected $sourcePath;

    /**
     * @var string|null
     */
    protected $sourceProductId;

    /**
     * @var string|null
     */
    protected $sourceCategoryId;

    /**
     * @var string|null
     */
    protected $redirectType;

    /**
     * @var string|null
     */
    protected $redirectUrl;

    /**
     * @var string|null
     */
    protected $redirectSalesChannelDomainId;

    /**
     * @var string|null
     */
    protected $redirectPath;

    /**
     * @var string|null
     */
    protected $redirectProductId;

    /**
     * @var string|null
     */
    protected $redirectCategoryId;

    /**
     * @var bool|null
     */
    protected $hasDeviatingRedirectSalesChannelDomain;

    /**
     * @var string|null
     */
    protected $deviatingRedirectSalesChannelDomainId;

    /**
     * @var SalesChannelDomainEntity|null
     */
    protected $sourceSalesChannelDomain;

    /**
     * @var ProductEntity|null
     */
    protected $sourceProduct;

    /**
     * @var CategoryEntity|null
     */
    protected $sourceCategory;

    /**
     * @var SalesChannelDomainEntity|null
     */
    protected $redirectSalesChannelDomain;

    /**
     * @var SalesChannelDomainEntity|null
     */
    protected $deviatingRedirectSalesChannelDomain;

    /**
     * @var ProductEntity|null
     */
    protected $redirectProduct;

    /**
     * @var CategoryEntity|null
     */
    protected $redirectCategory;

    /**
     * @var bool|null
     */
    protected $parameterForwarding;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getRedirectHttpStatusCode(): ?string
    {
        return $this->redirectHttpStatusCode;
    }

    public function setRedirectHttpStatusCode(?string $redirectHttpStatusCode): self
    {
        $this->redirectHttpStatusCode = $redirectHttpStatusCode;

        return $this;
    }

    public function getSourceType(): ?string
    {
        return $this->sourceType;
    }

    public function setSourceType(?string $sourceType): self
    {
        $this->sourceType = $sourceType;

        return $this;
    }

    public function getHasSourceSalesChannelDomainRestriction(): ?bool
    {
        return $this->hasSourceSalesChannelDomainRestriction;
    }

    public function setHasSourceSalesChannelDomainRestriction(?bool $hasSourceSalesChannelDomainRestriction): self
    {
        $this->hasSourceSalesChannelDomainRestriction = $hasSourceSalesChannelDomainRestriction;

        return $this;
    }

    public function getSourceSalesChannelDomainRestrictionIds(): ?array
    {
        return $this->sourceSalesChannelDomainRestrictionIds;
    }

    public function setSourceSalesChannelDomainRestrictionIds(?array $sourceSalesChannelDomainRestrictionIds): self
    {
        $this->sourceSalesChannelDomainRestrictionIds = $sourceSalesChannelDomainRestrictionIds;

        return $this;
    }

    public function getSourceSalesChannelDomainId(): ?string
    {
        return $this->sourceSalesChannelDomainId;
    }

    public function setSourceSalesChannelDomainId(?string $sourceSalesChannelDomainId): self
    {
        $this->sourceSalesChannelDomainId = $sourceSalesChannelDomainId;

        return $this;
    }

    public function getSourcePath(): ?string
    {
        return $this->sourcePath;
    }

    public function setSourcePath(?string $sourcePath): self
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    public function getSourceProductId(): ?string
    {
        return $this->sourceProductId;
    }

    public function setSourceProductId(?string $sourceProductId): self
    {
        $this->sourceProductId = $sourceProductId;

        return $this;
    }

    public function getSourceCategoryId(): ?string
    {
        return $this->sourceCategoryId;
    }

    public function setSourceCategoryId(?string $sourceCategoryId): self
    {
        $this->sourceCategoryId = $sourceCategoryId;

        return $this;
    }

    public function getRedirectType(): ?string
    {
        return $this->redirectType;
    }

    public function setRedirectType(?string $redirectType): self
    {
        $this->redirectType = $redirectType;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getRedirectSalesChannelDomainId(): ?string
    {
        return $this->redirectSalesChannelDomainId;
    }

    public function setRedirectSalesChannelDomainId(?string $redirectSalesChannelDomainId): self
    {
        $this->redirectSalesChannelDomainId = $redirectSalesChannelDomainId;

        return $this;
    }

    public function getRedirectPath(): ?string
    {
        return $this->redirectPath;
    }

    public function setRedirectPath(?string $redirectPath): self
    {
        $this->redirectPath = $redirectPath;

        return $this;
    }

    public function getRedirectProductId(): ?string
    {
        return $this->redirectProductId;
    }

    public function setRedirectProductId(?string $redirectProductId): self
    {
        $this->redirectProductId = $redirectProductId;

        return $this;
    }

    public function getRedirectCategoryId(): ?string
    {
        return $this->redirectCategoryId;
    }

    public function setRedirectCategoryId(?string $redirectCategoryId): self
    {
        $this->redirectCategoryId = $redirectCategoryId;

        return $this;
    }

    public function getHasDeviatingRedirectSalesChannelDomain(): ?bool
    {
        return $this->hasDeviatingRedirectSalesChannelDomain;
    }

    public function setHasDeviatingRedirectSalesChannelDomain(?bool $hasDeviatingRedirectSalesChannelDomain): self
    {
        $this->hasDeviatingRedirectSalesChannelDomain = $hasDeviatingRedirectSalesChannelDomain;

        return $this;
    }

    public function getDeviatingRedirectSalesChannelDomainId(): ?string
    {
        return $this->deviatingRedirectSalesChannelDomainId;
    }

    public function setDeviatingRedirectSalesChannelDomainId(?string $deviatingRedirectSalesChannelDomainId): self
    {
        $this->deviatingRedirectSalesChannelDomainId = $deviatingRedirectSalesChannelDomainId;

        return $this;
    }

    public function getSourceSalesChannelDomain(): ?SalesChannelDomainEntity
    {
        return $this->sourceSalesChannelDomain;
    }

    public function setSourceSalesChannelDomain(?SalesChannelDomainEntity $sourceSalesChannelDomain): self
    {
        $this->sourceSalesChannelDomain = $sourceSalesChannelDomain;

        return $this;
    }

    public function getSourceProduct(): ?ProductEntity
    {
        return $this->sourceProduct;
    }

    public function setSourceProduct(?ProductEntity $sourceProduct): self
    {
        $this->sourceProduct = $sourceProduct;

        return $this;
    }

    public function getSourceCategory(): ?CategoryEntity
    {
        return $this->sourceCategory;
    }

    public function setSourceCategory(?CategoryEntity $sourceCategory): self
    {
        $this->sourceCategory = $sourceCategory;

        return $this;
    }

    public function getRedirectSalesChannelDomain(): ?SalesChannelDomainEntity
    {
        return $this->redirectSalesChannelDomain;
    }

    public function setRedirectSalesChannelDomain(?SalesChannelDomainEntity $redirectSalesChannelDomain): self
    {
        $this->redirectSalesChannelDomain = $redirectSalesChannelDomain;

        return $this;
    }

    public function getDeviatingRedirectSalesChannelDomain(): ?SalesChannelDomainEntity
    {
        return $this->deviatingRedirectSalesChannelDomain;
    }

    public function setDeviatingRedirectSalesChannelDomain(?SalesChannelDomainEntity $deviatingRedirectSalesChannelDomain): self
    {
        $this->deviatingRedirectSalesChannelDomain = $deviatingRedirectSalesChannelDomain;

        return $this;
    }

    public function getRedirectProduct(): ?ProductEntity
    {
        return $this->redirectProduct;
    }

    public function setRedirectProduct(?ProductEntity $redirectProduct): self
    {
        $this->redirectProduct = $redirectProduct;

        return $this;
    }

    public function getRedirectCategory(): ?CategoryEntity
    {
        return $this->redirectCategory;
    }

    public function setRedirectCategory(?CategoryEntity $redirectCategory): self
    {
        $this->redirectCategory = $redirectCategory;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getParameterForwarding(): ?bool
    {
        return $this->parameterForwarding;
    }

    /**
     * @param bool|null $parameterForwarding
     * @return DreiscSeoRedirectEntity
     */
    public function setParameterForwarding(?bool $parameterForwarding): DreiscSeoRedirectEntity
    {
        $this->parameterForwarding = $parameterForwarding;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $jsonArray = [];
        foreach (get_object_vars($this) as $key => $value) {
            $jsonArray[$key] = $value;
        }

        return $jsonArray;
    }
}
