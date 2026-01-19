<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness;

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
    protected $name;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $telephone;

    /**
     * @param bool|null $active
     * @param string|null $name
     * @param string|null $url
     * @param string|null $telephone
     * @param string $settingContext
     */
    public function __construct(?bool $active, ?string $name, ?string $url, ?string $telephone, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->active = $active;
        $this->name = $name;
        $this->url = $url;
        $this->telephone = $telephone;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): GeneralStruct
    {
        $this->name = $name;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): GeneralStruct
    {
        $this->telephone = $telephone;

        return $this;
    }
}
