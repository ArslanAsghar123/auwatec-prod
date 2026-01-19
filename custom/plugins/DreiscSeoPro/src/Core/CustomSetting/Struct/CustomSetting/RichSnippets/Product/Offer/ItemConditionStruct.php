<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Offer;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class ItemConditionStruct extends AbstractCustomSettingStruct
{
    final const ITEM_CONDITION__NEW_CONDITION = 'NewCondition';
    final const ITEM_CONDITION__USED_CONDITION = 'UsedCondition';
    final const ITEM_CONDITION__REFURBISHED_CONDITION = 'RefurbishedCondition';
    final const ITEM_CONDITION__DAMAGED_CONDITION = 'DamagedCondition';

    /**
     * @var string|null
     */
    protected $defaultItemCondition;

    /**
     * @param string|null $defaultItemCondition
     * @param string $settingContext
     */
    public function __construct(?string $defaultItemCondition, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->defaultItemCondition = $defaultItemCondition;
    }

    public function getDefaultItemCondition(): ?string
    {
        return $this->defaultItemCondition;
    }

    public function setDefaultItemCondition(?string $defaultItemCondition): ItemConditionStruct
    {
        $this->defaultItemCondition = $defaultItemCondition;

        return $this;
    }
}
