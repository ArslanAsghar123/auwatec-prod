<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct;

use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\AiStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\BulkGeneratorStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\CanonicalStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\MetaTagsStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippetsStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RobotsTxtStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SeoUrlStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SerpStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SitemapStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\SocialMediaStruct;

class CustomSettingStruct extends AbstractCustomSettingStruct
{
    final public const SETTING_CONTEXT__DEFAULT = 'default';
    final public const SETTING_CONTEXT__SALES_CHANNEL = 'salesChannel';

    /**
     * @var MetaTagsStruct
     */
    protected $metaTags;

    /**
     * @var SocialMediaStruct
     */
    protected $socialMedia;

    /**
     * @var RichSnippetsStruct
     */
    protected $richSnippets;

    /**
     * @var SeoUrlStruct
     */
    protected $seoUrl;

    /**
     * @var SerpStruct
     */
    protected $serp;

    /**
     * @var SitemapStruct
     */
    protected $sitemap;

    /**
     * @var BulkGeneratorStruct
     */
    protected $bulkGenerator;

    /**
     * @var CanonicalStruct
     */
    protected $canonical;

    /**
     * @var RobotsTxtStruct
     */
    protected $robotsTxt;

    /**
     * @var AiStruct
     */
    protected $ai;

    /**
     * @param array $customSettings
     * @param string $settingContext
     */
    public function __construct(array $customSettings, string $settingContext = self::SETTING_CONTEXT__DEFAULT)
    {
        parent::__construct($settingContext);

        $this->metaTags = new MetaTagsStruct(!empty($customSettings['metaTags']) ? $customSettings['metaTags'] : [], $settingContext);
        $this->socialMedia = new SocialMediaStruct(!empty($customSettings['socialMedia']) ? $customSettings['socialMedia'] : [], $settingContext);
        $this->richSnippets = new RichSnippetsStruct(!empty($customSettings['richSnippets']) ? $customSettings['richSnippets'] : [], $settingContext);

        $this->seoUrl = new SeoUrlStruct(
            !empty($customSettings['seoUrl']['defaultSalesChannelId']) ? $customSettings['seoUrl']['defaultSalesChannelId'] : $this->setDefault(''),
            $settingContext
        );

        $this->serp = new SerpStruct(
            !empty($customSettings['serp']['defaultSalesChannelId']) ? $customSettings['serp']['defaultSalesChannelId'] : $this->setDefault(''),
            $settingContext
        );

        $this->sitemap = new SitemapStruct(!empty($customSettings['sitemap']) ? $customSettings['sitemap'] : [], $settingContext);

        $this->bulkGenerator = new BulkGeneratorStruct(!empty($customSettings['bulkGenerator']) ? $customSettings['bulkGenerator'] : [], $settingContext);

        $this->canonical = new CanonicalStruct(!empty($customSettings['canonical']) ? $customSettings['canonical'] : [], $settingContext);

        $this->robotsTxt = new RobotsTxtStruct(
            !empty($customSettings['robotsTxt']['content']) ? $customSettings['robotsTxt']['content'] : $this->setDefault(RobotsTxtStruct::CONTENT_DEFAULT),
            !empty($customSettings['robotsTxt']['addSitemap']) ? true : false,
            $settingContext
        );

        $this->ai = new AiStruct(
            !empty($customSettings['ai']) ? $customSettings['ai'] : [],
            $settingContext
        );
    }

    public function getMetaTags(): MetaTagsStruct
    {
        return $this->metaTags;
    }

    public function setMetaTags(MetaTagsStruct $metaTags): CustomSettingStruct
    {
        $this->metaTags = $metaTags;

        return $this;
    }

    public function getSocialMedia(): SocialMediaStruct
    {
        return $this->socialMedia;
    }

    public function setSocialMedia(SocialMediaStruct $socialMedia): CustomSettingStruct
    {
        $this->socialMedia = $socialMedia;
        return $this;
    }

    public function getRichSnippets(): RichSnippetsStruct
    {
        return $this->richSnippets;
    }

    public function setRichSnippets(RichSnippetsStruct $richSnippets): CustomSettingStruct
    {
        $this->richSnippets = $richSnippets;

        return $this;
    }

    public function getSeoUrl(): SeoUrlStruct
    {
        return $this->seoUrl;
    }

    public function setSeoUrl(SeoUrlStruct $seoUrl): CustomSettingStruct
    {
        $this->seoUrl = $seoUrl;

        return $this;
    }

    public function getSerp(): SerpStruct
    {
        return $this->serp;
    }

    public function setSerp(SerpStruct $serp): CustomSettingStruct
    {
        $this->serp = $serp;

        return $this;
    }

    /**
     * @return SitemapStruct
     */
    public function getSitemap(): SitemapStruct
    {
        return $this->sitemap;
    }

    /**
     * @param SitemapStruct $sitemap
     * @return CustomSettingStruct
     */
    public function setSitemap(SitemapStruct $sitemap): CustomSettingStruct
    {
        $this->sitemap = $sitemap;
        return $this;
    }

    public function getBulkGenerator(): BulkGeneratorStruct
    {
        return $this->bulkGenerator;
    }

    public function setBulkGenerator(BulkGeneratorStruct $bulkGenerator): CustomSettingStruct
    {
        $this->bulkGenerator = $bulkGenerator;

        return $this;
    }

    /**
     * @return CanonicalStruct
     */
    public function getCanonical(): CanonicalStruct
    {
        return $this->canonical;
    }

    /**
     * @param CanonicalStruct $canonical
     * @return CustomSettingStruct
     */
    public function setCanonical(CanonicalStruct $canonical): CustomSettingStruct
    {
        $this->canonical = $canonical;
        return $this;
    }

    /**
     * @return RobotsTxtStruct
     */
    public function getRobotsTxt(): RobotsTxtStruct
    {
        return $this->robotsTxt;
    }

    /**
     * @param RobotsTxtStruct $robotsTxt
     * @return CustomSettingStruct
     */
    public function setRobotsTxt(RobotsTxtStruct $robotsTxt): CustomSettingStruct
    {
        $this->robotsTxt = $robotsTxt;
        return $this;
    }

    /**
     * @return AiStruct
     */
    public function getAi(): AiStruct
    {
        return $this->ai;
    }

    /**
     * @param AiStruct $ai
     * @return CustomSettingStruct
     */
    public function setAi(AiStruct $ai): CustomSettingStruct
    {
        $this->ai = $ai;
        return $this;
    }
}
