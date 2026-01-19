<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class SeoUrlStruct extends AbstractCustomSettingStruct
{
    /**
     * @var string|null
     */
    protected $defaultSalesChannelId;

    /**
     * @param string $defaultSalesChannelId
     * @param string $settingContext
     */
    public function __construct(?string $defaultSalesChannelId, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->defaultSalesChannelId = $defaultSalesChannelId;
    }

    public function getDefaultSalesChannelId(): ?string
    {
        return $this->defaultSalesChannelId;
    }

    public function setDefaultSalesChannelId(?string $defaultSalesChannelId): SeoUrlStruct
    {
        $this->defaultSalesChannelId = $defaultSalesChannelId;

        return $this;
    }
}
