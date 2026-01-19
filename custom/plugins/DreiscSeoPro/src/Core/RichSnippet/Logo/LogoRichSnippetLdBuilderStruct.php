<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Logo;

use DreiscSeoPro\Core\CustomSetting\Struct\CustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class LogoRichSnippetLdBuilderStruct extends DefaultStruct
{
    /**
     * @var CustomSettingStruct
     */
    protected $customSetting;

    /**
     * @var SalesChannelEntity
     */
    protected $salesChannelEntity;

    public function __construct(CustomSettingStruct $customSetting, SalesChannelEntity $salesChannelEntity)
    {
        $this->customSetting = $customSetting;
        $this->salesChannelEntity = $salesChannelEntity;
    }

    public function getCustomSetting(): CustomSettingStruct
    {
        return $this->customSetting;
    }

    public function setCustomSetting(CustomSettingStruct $customSetting): LogoRichSnippetLdBuilderStruct
    {
        $this->customSetting = $customSetting;

        return $this;
    }

    public function getSalesChannelEntity(): SalesChannelEntity
    {
        return $this->salesChannelEntity;
    }

    public function setSalesChannelEntity(SalesChannelEntity $salesChannelEntity): LogoRichSnippetLdBuilderStruct
    {
        $this->salesChannelEntity = $salesChannelEntity;

        return $this;
    }
}
