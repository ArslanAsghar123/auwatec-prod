<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;


use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Canonical\GeneralStruct;

class CanonicalStruct extends AbstractCustomSettingStruct
{
    /**
     * @var GeneralStruct
     */
    protected $general;

    /**
     * @param array $canonicalSettings
     * @param string $settingContext
     */
    public function __construct(array $canonicalSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->general = new GeneralStruct(
            isset($canonicalSettings['general']['parentCanonicalInheritance']) && is_bool($canonicalSettings['general']['parentCanonicalInheritance']) ? $canonicalSettings['general']['parentCanonicalInheritance'] : null,
            $settingContext
        );
    }

    public function getGeneral(): GeneralStruct
    {
        return $this->general;
    }

    public function setGeneral(GeneralStruct $general): CanonicalStruct
    {
        $this->general = $general;

        return $this;
    }
}
