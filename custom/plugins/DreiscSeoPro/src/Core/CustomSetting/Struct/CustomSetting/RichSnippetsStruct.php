<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\BreadcrumbStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\GeneralStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusinessStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LogoStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\ProductStruct;

class RichSnippetsStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__GENERAL_ACTIVE = true;

    /**
     * @var GeneralStruct
     */
    protected $general;

    /**
     * @var ProductStruct
     */
    protected $product;

    /**
     * @var BreadcrumbStruct
     */
    protected $breadcrumb;

    /**
     * @var LogoStruct
     */
    protected $logo;

    /**
     * @var LocalBusinessStruct
     */
    protected $localBusiness;

    /**
     * @param array $richSnippetsSettings
     * @param string $settingContext
     */
    public function __construct(array $richSnippetsSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->general = new GeneralStruct(
            isset($richSnippetsSettings['general']['active']) && is_bool($richSnippetsSettings['general']['active']) ? $richSnippetsSettings['general']['active'] : $this->setDefault(self::DEFAULT__GENERAL_ACTIVE),
            $settingContext
        );

        $this->breadcrumb = new BreadcrumbStruct(
            !empty($richSnippetsSettings['breadcrumb']) ? $richSnippetsSettings['breadcrumb'] : [],
            $settingContext
        );

        $this->product = new ProductStruct(
            !empty($richSnippetsSettings['product']) ? $richSnippetsSettings['product'] : [],
            $settingContext
        );

        $this->logo = new LogoStruct(
            !empty($richSnippetsSettings['logo']) ? $richSnippetsSettings['logo'] : [],
            $settingContext
        );

        $this->localBusiness = new LocalBusinessStruct(
            !empty($richSnippetsSettings['localBusiness']) ? $richSnippetsSettings['localBusiness'] : [],
            $settingContext
        );
    }

    public function getGeneral(): GeneralStruct
    {
        return $this->general;
    }

    public function setGeneral(GeneralStruct $general): RichSnippetsStruct
    {
        $this->general = $general;

        return $this;
    }

    public function getBreadcrumb(): BreadcrumbStruct
    {
        return $this->breadcrumb;
    }

    public function setBreadcrumb(BreadcrumbStruct $breadcrumb): RichSnippetsStruct
    {
        $this->breadcrumb = $breadcrumb;

        return $this;
    }

    public function getProduct(): ProductStruct
    {
        return $this->product;
    }

    public function setProduct(ProductStruct $product): RichSnippetsStruct
    {
        $this->product = $product;

        return $this;
    }

    public function getLogo(): LogoStruct
    {
        return $this->logo;
    }

    public function setLogo(LogoStruct $logo): RichSnippetsStruct
    {
        $this->logo = $logo;
        return $this;
    }

    public function getLocalBusiness(): LocalBusinessStruct
    {
        return $this->localBusiness;
    }

    public function setLocalBusiness(LocalBusinessStruct $localBusiness): RichSnippetsStruct
    {
        $this->localBusiness = $localBusiness;
        return $this;
    }
}
