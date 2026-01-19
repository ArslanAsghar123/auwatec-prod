<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Offer;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class SellerStruct extends AbstractCustomSettingStruct
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @param string|null $name
     * @param string $settingContext
     */
    public function __construct(?string $name, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): SellerStruct
    {
        $this->name = $name;

        return $this;
    }
}
