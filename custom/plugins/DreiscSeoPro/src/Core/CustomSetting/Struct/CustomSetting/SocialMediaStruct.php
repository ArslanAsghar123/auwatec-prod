<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SocialMedia\FacebookDescriptionStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SocialMedia\FacebookTitleStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SocialMedia\TwitterDescriptionStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SocialMedia\TwitterTitleStruct;

class SocialMediaStruct extends AbstractCustomSettingStruct
{
    /**
     * @var FacebookTitleStruct
     */
    protected $facebookTitle;

    /**
     * @var FacebookDescriptionStruct
     */
    protected $facebookDescription;

    /**
     * @var TwitterTitleStruct
     */
    protected $twitterTitle;

    /**
     * @var TwitterDescriptionStruct
     */
    protected $twitterDescription;

    /**
     * @param array $socialMediaSettings
     * @param string $settingContext
     */
    public function __construct(array $socialMediaSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->facebookTitle = new FacebookTitleStruct(!empty($socialMediaSettings['facebookTitle']) ? $socialMediaSettings['facebookTitle'] : [], $settingContext);
        $this->facebookDescription = new FacebookDescriptionStruct(!empty($socialMediaSettings['facebookDescription']) ? $socialMediaSettings['facebookDescription'] : [], $settingContext);
        $this->twitterTitle = new TwitterTitleStruct(!empty($socialMediaSettings['twitterTitle']) ? $socialMediaSettings['twitterTitle'] : [], $settingContext);
        $this->twitterDescription = new TwitterDescriptionStruct(!empty($socialMediaSettings['twitterDescription']) ? $socialMediaSettings['twitterDescription'] : [], $settingContext);
    }

    public function getFacebookTitle(): FacebookTitleStruct
    {
        return $this->facebookTitle;
    }

    public function setFacebookTitle(FacebookTitleStruct $facebookTitle): SocialMediaStruct
    {
        $this->facebookTitle = $facebookTitle;
        return $this;
    }

    public function getFacebookDescription(): FacebookDescriptionStruct
    {
        return $this->facebookDescription;
    }

    public function setFacebookDescription(FacebookDescriptionStruct $facebookDescription): SocialMediaStruct
    {
        $this->facebookDescription = $facebookDescription;
        return $this;
    }

    public function getTwitterTitle(): TwitterTitleStruct
    {
        return $this->twitterTitle;
    }

    public function setTwitterTitle(TwitterTitleStruct $twitterTitle): SocialMediaStruct
    {
        $this->twitterTitle = $twitterTitle;
        return $this;
    }

    public function getTwitterDescription(): TwitterDescriptionStruct
    {
        return $this->twitterDescription;
    }

    public function setTwitterDescription(TwitterDescriptionStruct $twitterDescription): SocialMediaStruct
    {
        $this->twitterDescription = $twitterDescription;
        return $this;
    }
}
