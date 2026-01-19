<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Product;

use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Page\Product\Review\ReviewLoaderResult;

class ProductRichSnippetLdBuilderStruct extends DefaultStruct
{
    /**
     * @var CustomSettingStruct
     */
    protected $customSetting;

    /**
     * @var SalesChannelProductEntity
     */
    protected $salesChannelProductEntity;

    /**
     * @var SalesChannelEntity
     */
    protected $salesChannelEntity;

    /**
     * @var CurrencyEntity
     */
    protected $currencyEntity;

    /**
     * @var ReviewLoaderResult
     */
    protected $reviewLoaderResult;

    /**
     * @var string|null
     */
    protected $salesChannelDomainId;

    /**
     * @var SalesChannelContext
     */
    protected $salesChannelContext;

    /**
     * @param string|null $salesChannelDomainId
     */
    public function __construct(CustomSettingStruct $customSetting, SalesChannelProductEntity $salesChannelProductEntity, SalesChannelEntity $salesChannelEntity, CurrencyEntity $currencyEntity, ReviewLoaderResult $reviewLoaderResult, SalesChannelContext $salesChannelContext, string $salesChannelDomainId)
    {
        $this->customSetting = $customSetting;
        $this->salesChannelProductEntity = $salesChannelProductEntity;
        $this->salesChannelEntity = $salesChannelEntity;
        $this->currencyEntity = $currencyEntity;
        $this->reviewLoaderResult = $reviewLoaderResult;
        $this->salesChannelContext = $salesChannelContext;
        $this->salesChannelDomainId = $salesChannelDomainId;
    }

    public function getCustomSetting(): CustomSettingStruct
    {
        return $this->customSetting;
    }

    public function setCustomSetting(CustomSettingStruct $customSetting): ProductRichSnippetLdBuilderStruct
    {
        $this->customSetting = $customSetting;

        return $this;
    }

    public function getSalesChannelProductEntity(): SalesChannelProductEntity
    {
        return $this->salesChannelProductEntity;
    }

    public function setSalesChannelProductEntity(SalesChannelProductEntity $salesChannelProductEntity): ProductRichSnippetLdBuilderStruct
    {
        $this->salesChannelProductEntity = $salesChannelProductEntity;

        return $this;
    }

    public function getSalesChannelEntity(): SalesChannelEntity
    {
        return $this->salesChannelEntity;
    }

    public function setSalesChannelEntity(SalesChannelEntity $salesChannelEntity): ProductRichSnippetLdBuilderStruct
    {
        $this->salesChannelEntity = $salesChannelEntity;

        return $this;
    }

    public function getCurrencyEntity(): CurrencyEntity
    {
        return $this->currencyEntity;
    }

    public function setCurrencyEntity(CurrencyEntity $currencyEntity): ProductRichSnippetLdBuilderStruct
    {
        $this->currencyEntity = $currencyEntity;

        return $this;
    }

    public function getReviewLoaderResult(): ReviewLoaderResult
    {
        return $this->reviewLoaderResult;
    }

    public function setReviewLoaderResult(ReviewLoaderResult $reviewLoaderResult): ProductRichSnippetLdBuilderStruct
    {
        $this->reviewLoaderResult = $reviewLoaderResult;

        return $this;
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return ProductRichSnippetLdBuilderStruct
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): ProductRichSnippetLdBuilderStruct
    {
        $this->salesChannelContext = $salesChannelContext;

        return $this;
    }

    public function getSalesChannelDomainId(): ?string
    {
        return $this->salesChannelDomainId;
    }

    public function setSalesChannelDomainId(?string $salesChannelDomainId): ProductRichSnippetLdBuilderStruct
    {
        $this->salesChannelDomainId = $salesChannelDomainId;

        return $this;
    }
}
