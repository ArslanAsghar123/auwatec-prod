<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Breadcrumb;

use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use Shopware\Core\Content\Category\Tree\Tree;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class BreadcrumbRichSnippetLdBuilderStruct extends DefaultStruct
{
    /**
     * @var CustomSettingStruct
     */
    protected $customSetting;

    /**
     * @var array
     */
    protected $plainBreadcrumb;

    /**
     * @var SalesChannelEntity
     */
    protected $salesChannelEntity;

    /**
     * @var string
     */
    protected $salesChannelDomainId;

    /**
     * @var SalesChannelProductEntity|null
     */
    protected $salesChannelProductEntity;

    /**
     * @var SalesChannelContext
     */
    protected $salesChannelContext;

    /**
     * @param SalesChannelProductEntity|null $salesChannelProductEntity
     */
    public function __construct(CustomSettingStruct $customSetting, array $plainBreadcrumb, SalesChannelEntity $salesChannelEntity, string $salesChannelDomainId, SalesChannelProductEntity $salesChannelProductEntity = null, SalesChannelContext $salesChannelContext)
    {
        $this->customSetting = $customSetting;
        $this->plainBreadcrumb = $plainBreadcrumb;
        $this->salesChannelEntity = $salesChannelEntity;
        $this->salesChannelDomainId = $salesChannelDomainId;
        $this->salesChannelProductEntity = $salesChannelProductEntity;
        $this->salesChannelContext = $salesChannelContext;
    }

    public function getCustomSetting(): CustomSettingStruct
    {
        return $this->customSetting;
    }

    public function setCustomSetting(CustomSettingStruct $customSetting): BreadcrumbRichSnippetLdBuilderStruct
    {
        $this->customSetting = $customSetting;
        return $this;
    }

    public function getPlainBreadcrumb(): array
    {
        return $this->plainBreadcrumb;
    }

    public function setPlainBreadcrumb(array $plainBreadcrumb): BreadcrumbRichSnippetLdBuilderStruct
    {
        $this->plainBreadcrumb = $plainBreadcrumb;

        return $this;
    }

    public function getSalesChannelEntity(): SalesChannelEntity
    {
        return $this->salesChannelEntity;
    }

    public function setSalesChannelEntity(SalesChannelEntity $salesChannelEntity): BreadcrumbRichSnippetLdBuilderStruct
    {
        $this->salesChannelEntity = $salesChannelEntity;

        return $this;
    }

    public function getSalesChannelDomainId(): string
    {
        return $this->salesChannelDomainId;
    }

    public function setSalesChannelDomainId(string $salesChannelDomainId): BreadcrumbRichSnippetLdBuilderStruct
    {
        $this->salesChannelDomainId = $salesChannelDomainId;

        return $this;
    }

    public function getSalesChannelProductEntity(): ?SalesChannelProductEntity
    {
        return $this->salesChannelProductEntity;
    }

    public function setSalesChannelProductEntity(?SalesChannelProductEntity $salesChannelProductEntity): BreadcrumbRichSnippetLdBuilderStruct
    {
        $this->salesChannelProductEntity = $salesChannelProductEntity;

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
     * @return BreadcrumbRichSnippetLdBuilderStruct
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): BreadcrumbRichSnippetLdBuilderStruct
    {
        $this->salesChannelContext = $salesChannelContext;
        return $this;
    }
}
