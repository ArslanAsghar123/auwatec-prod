<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Breadcrumb;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class GeneralStruct extends AbstractCustomSettingStruct
{
    /**
     * @var bool|null
     */
    protected $active;

    /**
     * @param bool|null $active
     * @param string $settingContext
     */
    public function __construct(?bool $active, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->active = $active;
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
}
