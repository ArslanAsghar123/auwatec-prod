<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\LocalBusiness\OpeningHoursSpecification;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class SpecificationStruct extends AbstractCustomSettingStruct
{
    /**
     * @var bool|null
     */
    protected $active;

    /**
     * @var string|null
     */
    protected $opens;

    /**
     * @var string|null
     */
    protected $closes;

    /**
     * @param bool|null $active
     * @param string|null $opens
     * @param string|null $closes
     * @param string $settingContext
     */
    public function __construct(?bool $active, ?string $opens, ?string $closes, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->active = $active;
        $this->opens = $opens;
        $this->closes = $closes;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): SpecificationStruct
    {
        $this->active = $active;
        return $this;
    }

    public function getOpens(): ?string
    {
        return $this->opens;
    }

    public function setOpens(?string $opens): SpecificationStruct
    {
        $this->opens = $opens;
        return $this;
    }

    public function getCloses(): ?string
    {
        return $this->closes;
    }

    public function setCloses(?string $closes): SpecificationStruct
    {
        $this->closes = $closes;
        return $this;
    }
}
