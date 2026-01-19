<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Breadcrumb;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class ProductStruct extends AbstractCustomSettingStruct
{
    final public const SHOW_IN_BREADCRUMB_MODE__NOT_DISPLAY = 'notDisplay';
    final public const SHOW_IN_BREADCRUMB_MODE__ONLY_SHOP = 'onlyShop';
    final public const SHOW_IN_BREADCRUMB_MODE__ONLY_JSON_LD = 'onlyJsonLd';
    final public const SHOW_IN_BREADCRUMB_MODE__SHOP_AND_JSON_LD = 'shopAndJsonLd';

    /**
     * @var string|null
     */
    protected $showInBreadcrumbMode;

    /**
     * @param string|null $showInBreadcrumbMode
     * @param string $settingContext
     */
    public function __construct(?string $showInBreadcrumbMode, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->showInBreadcrumbMode = $showInBreadcrumbMode;
    }

    public function getShowInBreadcrumbMode(): ?string
    {
        return $this->showInBreadcrumbMode;
    }

    public function setShowInBreadcrumbMode(?string $showInBreadcrumbMode): ProductStruct
    {
        $this->showInBreadcrumbMode = $showInBreadcrumbMode;

        return $this;
    }
}
