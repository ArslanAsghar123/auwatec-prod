<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Logo;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class GeneralStruct extends AbstractCustomSettingStruct
{
    /**
     * @var bool|null
     */
    protected $active;

    /**
     * @var string|null
     */
    protected $logo;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @param bool|null $active
     * @param string|null $logo
     * @param string|null $url
     * @param string $settingContext
     */
    public function __construct(?bool $active, ?string $logo, ?string $url, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->active = $active;
        $this->logo = $logo;
        $this->url = $url;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): GeneralStruct
    {
        $this->active = $active;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): GeneralStruct
    {
        $this->logo = $logo;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): GeneralStruct
    {
        $this->url = $url;

        return $this;
    }
}
