<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;


use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Sitemap\GeneralStruct;

class SitemapStruct extends AbstractCustomSettingStruct
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
            isset($canonicalSettings['general']['parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled'])
                && is_bool($canonicalSettings['general']['parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled']) ?
                $canonicalSettings['general']['parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled'] : null,
            isset($canonicalSettings['general']['hideInSitemapIfSeoUrlNotEqualCanonical'])
                && is_bool($canonicalSettings['general']['hideInSitemapIfSeoUrlNotEqualCanonical']) ?
                $canonicalSettings['general']['hideInSitemapIfSeoUrlNotEqualCanonical'] : null,
            isset($canonicalSettings['general']['hideInSitemapIfRobotsTagNoindex'])
                && is_bool($canonicalSettings['general']['hideInSitemapIfRobotsTagNoindex']) ?
                $canonicalSettings['general']['hideInSitemapIfRobotsTagNoindex'] : null,
            $settingContext
        );
    }

    public function getGeneral(): GeneralStruct
    {
        return $this->general;
    }

    public function setGeneral(GeneralStruct $general): SitemapStruct
    {
        $this->general = $general;

        return $this;
    }
}
