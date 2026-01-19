<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\KeywordsStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\MetaDescriptionStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\MetaTitleStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTags\RobotsTagStruct;

class MetaTagsStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__ROBOTS_TAG__DEFAULT_ROBOTS_TAG_PRODUCT = RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW;
    final const DEFAULT__ROBOTS_TAG__DEFAULT_ROBOTS_TAG_CATEGORY = RobotsTagStruct::ROBOTS_TAG__INDEX_FOLLOW;
    final const DEFAULT__ROBOTS_TAG__NO_INDEX_REQUEST_PARAMETER_CONFIG = 'sort!=name-asc; p>1; properties; rating; min-price; max-price; manufacturer';

    /**
     * @var MetaTitleStruct
     */
    protected $metaTitle;

    /**
     * @var MetaDescriptionStruct
     */
    protected $metaDescription;

    /**
     * @var KeywordsStruct
     */
    protected $keywords;

    /**
     * @var RobotsTagStruct
     */
    protected $robotsTag;

    /**
     * @param array $metaTagsSettings
     * @param string $settingContext
     */
    public function __construct(array $metaTagsSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->metaTitle = new MetaTitleStruct(!empty($metaTagsSettings['metaTitle']) ? $metaTagsSettings['metaTitle'] : [], $settingContext);
        $this->metaDescription = new MetaDescriptionStruct(!empty($metaTagsSettings['metaDescription']) ? $metaTagsSettings['metaDescription'] : [], $settingContext);
        $this->keywords = new KeywordsStruct(!empty($metaTagsSettings['keywords']) ? $metaTagsSettings['keywords'] : [], $settingContext);

        $this->robotsTag = new RobotsTagStruct(
            !empty($metaTagsSettings['robotsTag']['defaultRobotsTagProduct']) ?
                $metaTagsSettings['robotsTag']['defaultRobotsTagProduct'] :
                $this->setDefault(self::DEFAULT__ROBOTS_TAG__DEFAULT_ROBOTS_TAG_PRODUCT),

            !empty($metaTagsSettings['robotsTag']['defaultRobotsTagCategory']) ?
                $metaTagsSettings['robotsTag']['defaultRobotsTagCategory'] :
                $this->setDefault(self::DEFAULT__ROBOTS_TAG__DEFAULT_ROBOTS_TAG_CATEGORY),

            !empty($metaTagsSettings['robotsTag']['noIndexRequestParameterConfig']) ?
                $metaTagsSettings['robotsTag']['noIndexRequestParameterConfig'] :
                $this->setDefault(self::DEFAULT__ROBOTS_TAG__NO_INDEX_REQUEST_PARAMETER_CONFIG),
            $settingContext
        );
    }

    public function getMetaTitle(): MetaTitleStruct
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(MetaTitleStruct $metaTitle): MetaTagsStruct
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): MetaDescriptionStruct
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(MetaDescriptionStruct $metaDescription): MetaTagsStruct
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getKeywords(): KeywordsStruct
    {
        return $this->keywords;
    }

    public function setKeywords(KeywordsStruct $keywords): MetaTagsStruct
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getRobotsTag(): RobotsTagStruct
    {
        return $this->robotsTag;
    }

    public function setRobotsTag(RobotsTagStruct $robotsTag): MetaTagsStruct
    {
        $this->robotsTag = $robotsTag;
        return $this;
    }
}
