<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class RobotsTxtStruct extends AbstractCustomSettingStruct
{
    public const CONTENT_DEFAULT = "User-agent: *\nAllow: /\nDisallow: */?\nDisallow: */account/\nDisallow: */checkout/\nDisallow: */widgets/\nDisallow: */navigation/\nDisallow: */bundles/\n\nDisallow: */impressum$\nDisallow: */datenschutz$\nDisallow: */agb$";

    /**
     * @var string|null
     */
    protected $content;

    /**
     * @var boolean|null
     */
    protected $addSitemap;

    /**
     * @param string $defaultSalesChannelId
     * @param string $settingContext
     */
    public function __construct(?string $content, ?bool $addSitemap, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->content = $content;
        $this->addSitemap = $addSitemap;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return RobotsTxtStruct
     */
    public function setContent(?string $content): RobotsTxtStruct
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getAddSitemap(): ?bool
    {
        return $this->addSitemap;
    }

    /**
     * @param bool|null $addSitemap
     * @return RobotsTxtStruct
     */
    public function setAddSitemap(?bool $addSitemap): RobotsTxtStruct
    {
        $this->addSitemap = $addSitemap;
        return $this;
    }
}
