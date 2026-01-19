<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Common;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class LengthConfigStruct extends AbstractCustomSettingStruct
{
    /**
     * @var int|null
     */
    protected $recommendedLengthStart;

    /**
     * @var int|null
     */
    protected $recommendedLengthEnd;

    /**
     * @var int|null
     */
    protected $maxLength;

    /**
     * @param int|null $recommendedLengthStart
     * @param int|null $recommendedLengthEnd
     * @param int|null $maxLength
     * @param string $settingContext
     */
    public function __construct(?int $recommendedLengthStart, ?int $recommendedLengthEnd, ?int $maxLength, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->recommendedLengthStart = $recommendedLengthStart;
        $this->recommendedLengthEnd = $recommendedLengthEnd;
        $this->maxLength = $maxLength;
    }

    public function getRecommendedLengthStart(): ?int
    {
        return $this->recommendedLengthStart;
    }

    public function setRecommendedLengthStart(?int $recommendedLengthStart): LengthConfigStruct
    {
        $this->recommendedLengthStart = $recommendedLengthStart;

        return $this;
    }

    public function getRecommendedLengthEnd(): ?int
    {
        return $this->recommendedLengthEnd;
    }

    public function setRecommendedLengthEnd(?int $recommendedLengthEnd): LengthConfigStruct
    {
        $this->recommendedLengthEnd = $recommendedLengthEnd;

        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function setMaxLength(?int $maxLength): LengthConfigStruct
    {
        $this->maxLength = $maxLength;

        return $this;
    }
}
