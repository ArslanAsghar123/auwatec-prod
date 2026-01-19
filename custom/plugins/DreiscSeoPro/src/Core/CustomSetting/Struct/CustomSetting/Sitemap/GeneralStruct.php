<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Sitemap;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class GeneralStruct extends AbstractCustomSettingStruct
{
    /**
     * @var bool|null
     */
    protected ?bool $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled;

    /**
     * @var bool|null
     */
    protected ?bool $hideInSitemapIfSeoUrlNotEqualCanonical;

    /**
     * @var bool|null
     */
    protected ?bool $hideInSitemapIfRobotsTagNoindex;

    public function __construct(?bool $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled, ?bool $hideInSitemapIfSeoUrlNotEqualCanonical, ?bool $hideInSitemapIfRobotsTagNoindex, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled = $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled;
        $this->hideInSitemapIfSeoUrlNotEqualCanonical = $hideInSitemapIfSeoUrlNotEqualCanonical;
        $this->hideInSitemapIfRobotsTagNoindex = $hideInSitemapIfRobotsTagNoindex;
    }

    /**
     * @return bool|null
     */
    public function getParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled(): ?bool
    {
        return $this->parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled;
    }

    /**
     * @param bool|null $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled
     * @return GeneralStruct
     */
    public function setParentUrlInSitemapIfParentCanonicalInheritanceIsEnabled(?bool $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled): GeneralStruct
    {
        $this->parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled = $parentUrlInSitemapIfParentCanonicalInheritanceIsEnabled;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getHideInSitemapIfSeoUrlNotEqualCanonical(): ?bool
    {
        return $this->hideInSitemapIfSeoUrlNotEqualCanonical;
    }

    /**
     * @param bool|null $hideInSitemapIfSeoUrlNotEqualCanonical
     * @return \DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Canonical\GeneralStruct
     */
    public function setHideInSitemapIfSeoUrlNotEqualCanonical(?bool $hideInSitemapIfSeoUrlNotEqualCanonical): GeneralStruct
    {
        $this->hideInSitemapIfSeoUrlNotEqualCanonical = $hideInSitemapIfSeoUrlNotEqualCanonical;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getHideInSitemapIfRobotsTagNoindex(): ?bool
    {
        return $this->hideInSitemapIfRobotsTagNoindex;
    }

    /**
     * @param bool|null $hideInSitemapIfRobotsTagNoindex
     * @return GeneralStruct
     */
    public function setHideInSitemapIfRobotsTagNoindex(?bool $hideInSitemapIfRobotsTagNoindex): GeneralStruct
    {
        $this->hideInSitemapIfRobotsTagNoindex = $hideInSitemapIfRobotsTagNoindex;
        return $this;
    }
}
