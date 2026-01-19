<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class PriceValidUntilStruct extends AbstractCustomSettingStruct
{
    final const INTERVAL__NOT_DISPLAY = 'notDisplay';
    final const INTERVAL__TODAY = 'today';
    final const INTERVAL__1_DAY = '1day';
    final const INTERVAL__1_WEEK = '1week';
    final const INTERVAL__2_WEEK = '2week';
    final const INTERVAL__1_MONTH = '1month';
    final const INTERVAL__CUSTOM_DAYS = 'customDays';

    /**
     * @var string|null
     */
    protected $interval;

    /**
     * @var int|null
     */
    protected $customDays;

    /**
     * @param string|null $interval
     * @param int|null $customDays
     * @param string $settingContext
     */
    public function __construct(?string $interval, ?int $customDays, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->interval = $interval;
        $this->customDays = $customDays;
    }

    public function getInterval(): ?string
    {
        return $this->interval;
    }

    public function setInterval(?string $interval): PriceValidUntilStruct
    {
        $this->interval = $interval;

        return $this;
    }

    public function getCustomDays(): ?int
    {
        return $this->customDays;
    }

    public function setCustomDays(?int $customDays): PriceValidUntilStruct
    {
        $this->customDays = $customDays;

        return $this;
    }
}
