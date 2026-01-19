<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class GeneralStruct extends AbstractCustomSettingStruct
{
    /** Product number (recommended) */
    final const SKU_COMPILATION__PRODUCT_NUMBER = 'productNumber';

    /** Manufacturer number if available, otherwise product number */
    final const SKU_COMPILATION__MANUFACTURER_NUMBER__OTHERWISE__PRODUCT_NUMBER = 'manufacturerNumberOtherwiseProductNumber';

    /** Product number (recommended) */
    final const MPN_COMPILATION__PRODUCT_NUMBER = 'productNumber';

    /** Manufacturer number if available, otherwise product number (recommended) */
    final const MPN_COMPILATION__MANUFACTURER_NUMBER__OTHERWISE__PRODUCT_NUMBER = 'manufacturerNumberOtherwiseProductNumber';


    /**
     * @var string|null
     */
    protected $skuCompilation;

    /**
     * @var string|null
     */
    protected $mpnCompilation;

    /**
     * @param string|null $skuCompilation
     * @param string|null $mpnCompilation
     * @param string $settingContext
     */
    public function __construct(?string $skuCompilation, ?string $mpnCompilation, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->skuCompilation = $skuCompilation;
        $this->mpnCompilation = $mpnCompilation;
    }

    public function getSkuCompilation(): ?string
    {
        return $this->skuCompilation;
    }

    public function setSkuCompilation(?string $skuCompilation): GeneralStruct
    {
        $this->skuCompilation = $skuCompilation;

        return $this;
    }

    public function getMpnCompilation(): ?string
    {
        return $this->mpnCompilation;
    }

    public function setMpnCompilation(?string $mpnCompilation): GeneralStruct
    {
        $this->mpnCompilation = $mpnCompilation;

        return $this;
    }
}
