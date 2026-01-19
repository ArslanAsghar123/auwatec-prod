<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Logo\GeneralStruct as LogoGeneralStruct;

class LogoStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__GENERAL__ACTIVE = false;

    /**
     * @var LogoGeneralStruct
     */
    protected $general;

    /**
     * @param array $logoSettings
     * @param string $settingContext
     */
    public function __construct(array $logoSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->general = new LogoGeneralStruct(
            $logoSettings['general']['active'] ?? $this->setDefault(self::DEFAULT__GENERAL__ACTIVE),
            !empty($logoSettings['general']['logo']) ? $logoSettings['general']['logo'] : $this->setDefault(''),
            !empty($logoSettings['general']['url']) ? $logoSettings['general']['url'] : $this->setDefault(''),
            $settingContext
        );
    }

    public function getGeneral(): LogoGeneralStruct
    {
        return $this->general;
    }

    public function setGeneral(LogoGeneralStruct $general): BreadcrumbStruct
    {
        $this->general = $general;

        return $this;
    }
}
