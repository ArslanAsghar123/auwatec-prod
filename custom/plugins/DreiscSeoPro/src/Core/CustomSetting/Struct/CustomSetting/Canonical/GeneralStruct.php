<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Canonical;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class GeneralStruct extends AbstractCustomSettingStruct
{
    /**
     * @var bool|null
     */
    protected $parentCanonicalInheritance;

    /**
     * @param bool|null $parentCanonicalInheritance
     * @param string $settingContext
     */
    public function __construct(?bool $parentCanonicalInheritance, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->parentCanonicalInheritance = $parentCanonicalInheritance;
    }

    /**
     * @return bool|null
     */
    public function getParentCanonicalInheritance(): ?bool
    {
        return $this->parentCanonicalInheritance;
    }

    /**
     * @param bool|null $parentCanonicalInheritance
     * @return GeneralStruct
     */
    public function setParentCanonicalInheritance(?bool $parentCanonicalInheritance): GeneralStruct
    {
        $this->parentCanonicalInheritance = $parentCanonicalInheritance;
        return $this;
    }
}
