<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\Common\LengthConfigStruct;

class RobotsTagStruct extends AbstractCustomSettingStruct
{
    final const ROBOTS_TAG__INDEX_FOLLOW = 'index,follow';
    final const ROBOTS_TAG__INDEX_NOFOLLOW = 'index,nofollow';
    final const ROBOTS_TAG__NOINDEX_FOLLOW = 'noindex,follow';
    final const ROBOTS_TAG__NOINDEX_NOFOLLOW = 'noindex,nofollow';

    final const VALID_ROBOTS_TAGS = [
        self::ROBOTS_TAG__INDEX_FOLLOW,
        self::ROBOTS_TAG__INDEX_NOFOLLOW,
        self::ROBOTS_TAG__NOINDEX_FOLLOW,
        self::ROBOTS_TAG__NOINDEX_NOFOLLOW,
    ];

    /**
     * @var string
     */
    protected $defaultRobotsTagProduct;

    /**
     * @var string
     */
    protected $defaultRobotsTagCategory;

    /**
     * @var string
     */
    protected $noIndexRequestParameterConfig;

    /**
     * @param string|null $defaultRobotsTagProduct
     * @param string|null $defaultRobotsTagCategory
     * @param string $settingContext
     */
    public function __construct(?string $defaultRobotsTagProduct, ?string $defaultRobotsTagCategory, ?string $noIndexRequestParameterConfig, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->defaultRobotsTagProduct = $defaultRobotsTagProduct;
        $this->defaultRobotsTagCategory = $defaultRobotsTagCategory;
        $this->noIndexRequestParameterConfig = $noIndexRequestParameterConfig;
    }

    public function getDefaultRobotsTagProduct(): string
    {
        return $this->defaultRobotsTagProduct;
    }

    public function setDefaultRobotsTagProduct(string $defaultRobotsTagProduct): RobotsTagStruct
    {
        $this->defaultRobotsTagProduct = $defaultRobotsTagProduct;

        return $this;
    }

    public function getDefaultRobotsTagCategory(): string
    {
        return $this->defaultRobotsTagCategory;
    }

    public function setDefaultRobotsTagCategory(string $defaultRobotsTagCategory): RobotsTagStruct
    {
        $this->defaultRobotsTagCategory = $defaultRobotsTagCategory;

        return $this;
    }

    public function getNoIndexRequestParameterConfig(): string
    {
        return $this->noIndexRequestParameterConfig;
    }

    public function setNoIndexRequestParameterConfig(string $noIndexRequestParameterConfig): RobotsTagStruct
    {
        $this->noIndexRequestParameterConfig = $noIndexRequestParameterConfig;

        return $this;
    }
}
