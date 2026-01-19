<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Breadcrumb\GeneralStruct as BreadcrumbGeneralStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Breadcrumb\ProductStruct as BreadcrumbProductStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Breadcrumb\HomeStruct;

class BreadcrumbStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__GENERAL__ACTIVE = true;
    final const DEFAULT__HOME__SHOW_IN_BREADCRUMB_MODE = HomeStruct::SHOW_IN_BREADCRUMB_MODE__NOT_DISPLAY;
    final const DEFAULT__PRODUCT__SHOW_IN_BREADCRUMB_MODE = BreadcrumbProductStruct::SHOW_IN_BREADCRUMB_MODE__NOT_DISPLAY;

    /**
     * @var BreadcrumbGeneralStruct
     */
    protected $general;

    /**
     * @var HomeStruct
     */
    protected $home;

    /**
     * @var BreadcrumbProductStruct
     */
    protected $product;

    /**
     * @param array $breadcrumbSettings
     * @param string $settingContext
     */
    public function __construct(array $breadcrumbSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->general = new BreadcrumbGeneralStruct(
            $breadcrumbSettings['general']['active'] ?? $this->setDefault(self::DEFAULT__GENERAL__ACTIVE),
            $settingContext
        );

        $this->home = new HomeStruct(
            !empty($breadcrumbSettings['home']['showInBreadcrumbMode']) ? $breadcrumbSettings['home']['showInBreadcrumbMode'] : $this->setDefault(self::DEFAULT__HOME__SHOW_IN_BREADCRUMB_MODE),
            $settingContext
        );

        $this->product = new BreadcrumbProductStruct(
            !empty($breadcrumbSettings['product']['showInBreadcrumbMode']) ? $breadcrumbSettings['product']['showInBreadcrumbMode'] : $this->setDefault(self::DEFAULT__PRODUCT__SHOW_IN_BREADCRUMB_MODE),
            $settingContext
        );
    }

    public function getGeneral(): BreadcrumbGeneralStruct
    {
        return $this->general;
    }

    /**
     * @return BreadcrumbStruct
     */
    public function setGeneral(BreadcrumbGeneralStruct $general): ProductStruct
    {
        $this->general = $general;

        return $this;
    }

    public function getHome(): HomeStruct
    {
        return $this->home;
    }

    public function setHome(HomeStruct $home): BreadcrumbStruct
    {
        $this->home = $home;

        return $this;
    }

    public function getProduct(): BreadcrumbProductStruct
    {
        return $this->product;
    }

    public function setProduct(BreadcrumbProductStruct $product): BreadcrumbStruct
    {
        $this->product = $product;

        return $this;
    }
}
