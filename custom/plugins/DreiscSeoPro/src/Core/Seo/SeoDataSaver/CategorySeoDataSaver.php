<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category\FacebookDescriptionSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category\FacebookTitleSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category\MetaDescriptionSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category\MetaTitleSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category\RobotsTagSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category\TwitterDescriptionSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category\TwitterTitleSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\Category\UrlSaver;
use DreiscSeoPro\Core\Seo\SeoDataSaver\Saver\SaverInterface;

class CategorySeoDataSaver extends AbstractSeoDataSaver
{
    /**
     * @var MetaTitleSaver
     */
    protected $metaTitleSaver;

    /**
     * @var MetaDescriptionSaver
     */
    protected $metaDescriptionSaver;

    /**
     * @var UrlSaver
     */
    protected $urlSaver;

    /**
     * @var RobotsTagSaver
     */
    protected $robotsTagSaver;

    /**
     * @var FacebookTitleSaver
     */
    protected $facebookTitleSaver;

    /**
     * @var FacebookDescriptionSaver
     */
    protected $facebookDescriptionSaver;

    /**
     * @var TwitterTitleSaver
     */
    protected $twitterTitleSaver;

    /**
     * @var TwitterDescriptionSaver
     */
    protected $twitterDescriptionSaver;

    public function __construct(MetaTitleSaver $metaTitleSaver, MetaDescriptionSaver $metaDescriptionSaver, UrlSaver $urlSaver, RobotsTagSaver $robotsTagSaver, FacebookTitleSaver $facebookTitleSaver, FacebookDescriptionSaver $facebookDescriptionSaver, TwitterTitleSaver $twitterTitleSaver, TwitterDescriptionSaver $twitterDescriptionSaver)
    {
        $this->metaTitleSaver = $metaTitleSaver;
        $this->metaDescriptionSaver = $metaDescriptionSaver;
        $this->urlSaver = $urlSaver;
        $this->robotsTagSaver = $robotsTagSaver;
        $this->facebookTitleSaver = $facebookTitleSaver;
        $this->facebookDescriptionSaver = $facebookDescriptionSaver;
        $this->twitterTitleSaver = $twitterTitleSaver;
        $this->twitterDescriptionSaver = $twitterDescriptionSaver;
    }

    /**
     * @return string
     */
    protected function getArea(): string
    {
        return DreiscSeoBulkEnum::AREA__CATEGORY;
    }

    /**
     * @return SaverInterface
     */
    protected function getMetaTitleSaver(): SaverInterface
    {
        return $this->metaTitleSaver;
    }

    /**
     * @return SaverInterface
     */
    protected function getMetaDescriptionSaver(): SaverInterface
    {
        return $this->metaDescriptionSaver;
    }

    /**
     * @return SaverInterface
     */
    protected function getUrlSaver(): SaverInterface
    {
        return $this->urlSaver;
    }

    /**
     * @return SaverInterface
     */
    protected function getRobotsTagSaver(): SaverInterface
    {
        return $this->robotsTagSaver;
    }

    /**
     * @return SaverInterface
     */
    protected function getFacebookTitleSaver(): SaverInterface
    {
        return $this->facebookTitleSaver;
    }

    /**
     * @return SaverInterface
     */
    protected function getFacebookDescriptionSaver(): SaverInterface
    {
        return $this->facebookDescriptionSaver;
    }

    /**
     * @return SaverInterface
     */
    protected function getTwitterTitleSaver(): SaverInterface
    {
        return $this->twitterTitleSaver;
    }

    /**
     * @return SaverInterface
     */
    protected function getTwitterDescriptionSaver(): SaverInterface
    {
        return $this->twitterDescriptionSaver;
    }
}
