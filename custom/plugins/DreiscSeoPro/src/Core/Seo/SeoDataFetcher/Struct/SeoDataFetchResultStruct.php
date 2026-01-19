<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataFetcher\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class SeoDataFetchResultStruct extends DefaultStruct
{
    /**
     * @var string|null
     */
    protected $metaTitle = null;

    /**
     * @var bool|null
     */
    protected $isInheritedMetaTitle = null;

    /**
     * @var string|null
     */
    protected $metaDescription = null;

    /**
     * @var bool|null
     */
    protected $isInheritedMetaDescription = null;

    /**
     * @var string|null
     */
    protected $url = null;

    /**
     * @var bool|null
     */
    protected $isInheritedUrl = null;

    /**
     * @var bool|null
     */
    protected $isModifiedUrl = null;

    /**
     * @var string|null
     */
    protected $robotsTag = null;

    /**
     * @var bool|null
     */
    protected $isInheritedRobotsTag = null;

    /**
     * @var string|null
     */
    protected $canonicalLinkType = null;

    /**
     * @var string|null
     */
    protected $canonicalLinkReference = null;

    /**
     * @var bool|null
     */
    protected $isInheritedCanonicalLink = null;

    /**
     * @var string|null
     */
    protected $facebookTitle = null;

    /**
     * @var bool|null
     */
    protected $isInheritedFacebookTitle = null;

    /**
     * @var string|null
     */
    protected $facebookDescription = null;

    /**
     * @var bool|null
     */
    protected $isInheritedFacebookDescription = null;

    /**
     * @var string|null
     */
    protected $facebookImage = null;

    /**
     * @var string|null
     */
    protected $twitterTitle = null;

    /**
     * @var bool|null
     */
    protected $isInheritedTwitterTitle = null;

    /**
     * @var string|null
     */
    protected $twitterDescription = null;

    /**
     * @var bool|null
     */
    protected $isInheritedTwitterDescription = null;

    /**
     * @var string|null
     */
    protected $twitterImage = null;

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): SeoDataFetchResultStruct
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    public function isInheritedMetaTitle(): ?bool
    {
        return $this->isInheritedMetaTitle;
    }

    public function setIsInheritedMetaTitle(?bool $isInheritedMetaTitle): SeoDataFetchResultStruct
    {
        $this->isInheritedMetaTitle = $isInheritedMetaTitle;
        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): SeoDataFetchResultStruct
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    public function isInheritedMetaDescription(): ?bool
    {
        return $this->isInheritedMetaDescription;
    }

    public function setIsInheritedMetaDescription(?bool $isInheritedMetaDescription): SeoDataFetchResultStruct
    {
        $this->isInheritedMetaDescription = $isInheritedMetaDescription;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): SeoDataFetchResultStruct
    {
        $this->url = $url;
        return $this;
    }

    public function isInheritedUrl(): ?bool
    {
        return $this->isInheritedUrl;
    }

    public function setIsInheritedUrl(?bool $isInheritedUrl): SeoDataFetchResultStruct
    {
        $this->isInheritedUrl = $isInheritedUrl;
        return $this;
    }

    public function isModifiedUrl(): ?bool
    {
        return $this->isModifiedUrl;
    }

    public function setIsModifiedUrl(?bool $isModifiedUrl): SeoDataFetchResultStruct
    {
        $this->isModifiedUrl = $isModifiedUrl;
        return $this;
    }

    public function getRobotsTag(): ?string
    {
        return $this->robotsTag;
    }

    public function setRobotsTag(?string $robotsTag): SeoDataFetchResultStruct
    {
        $this->robotsTag = $robotsTag;
        return $this;
    }

    public function isInheritedRobotsTag(): ?bool
    {
        return $this->isInheritedRobotsTag;
    }

    public function setIsInheritedRobotsTag(?bool $isInheritedRobotsTag): SeoDataFetchResultStruct
    {
        $this->isInheritedRobotsTag = $isInheritedRobotsTag;
        return $this;
    }

    public function getCanonicalLinkType(): ?string
    {
        return $this->canonicalLinkType;
    }

    public function setCanonicalLinkType(?string $canonicalLinkType): SeoDataFetchResultStruct
    {
        $this->canonicalLinkType = $canonicalLinkType;
        return $this;
    }

    public function getCanonicalLinkReference(): ?string
    {
        return $this->canonicalLinkReference;
    }

    public function setCanonicalLinkReference(?string $canonicalLinkReference): SeoDataFetchResultStruct
    {
        $this->canonicalLinkReference = $canonicalLinkReference;
        return $this;
    }

    public function isInheritedCanonicalLink(): ?bool
    {
        return $this->isInheritedCanonicalLink;
    }

    public function setIsInheritedCanonicalLink(?bool $isInheritedCanonicalLink): SeoDataFetchResultStruct
    {
        $this->isInheritedCanonicalLink = $isInheritedCanonicalLink;
        return $this;
    }

    public function getFacebookTitle(): ?string
    {
        return $this->facebookTitle;
    }

    public function setFacebookTitle(?string $facebookTitle): SeoDataFetchResultStruct
    {
        $this->facebookTitle = $facebookTitle;
        return $this;
    }

    public function isInheritedFacebookTitle(): ?bool
    {
        return $this->isInheritedFacebookTitle;
    }

    public function setIsInheritedFacebookTitle(?bool $isInheritedFacebookTitle): SeoDataFetchResultStruct
    {
        $this->isInheritedFacebookTitle = $isInheritedFacebookTitle;
        return $this;
    }

    public function getFacebookDescription(): ?string
    {
        return $this->facebookDescription;
    }

    public function setFacebookDescription(?string $facebookDescription): SeoDataFetchResultStruct
    {
        $this->facebookDescription = $facebookDescription;
        return $this;
    }

    public function isInheritedFacebookDescription(): ?bool
    {
        return $this->isInheritedFacebookDescription;
    }

    public function setIsInheritedFacebookDescription(?bool $isInheritedFacebookDescription): SeoDataFetchResultStruct
    {
        $this->isInheritedFacebookDescription = $isInheritedFacebookDescription;
        return $this;
    }

    public function getFacebookImage(): ?string
    {
        return $this->facebookImage;
    }

    public function setFacebookImage(?string $facebookImage): SeoDataFetchResultStruct
    {
        $this->facebookImage = $facebookImage;
        return $this;
    }

    public function getTwitterTitle(): ?string
    {
        return $this->twitterTitle;
    }

    public function setTwitterTitle(?string $twitterTitle): SeoDataFetchResultStruct
    {
        $this->twitterTitle = $twitterTitle;
        return $this;
    }

    public function isInheritedTwitterTitle(): ?bool
    {
        return $this->isInheritedTwitterTitle;
    }

    public function setIsInheritedTwitterTitle(?bool $isInheritedTwitterTitle): SeoDataFetchResultStruct
    {
        $this->isInheritedTwitterTitle = $isInheritedTwitterTitle;
        return $this;
    }

    public function getTwitterDescription(): ?string
    {
        return $this->twitterDescription;
    }

    public function setTwitterDescription(?string $twitterDescription): SeoDataFetchResultStruct
    {
        $this->twitterDescription = $twitterDescription;
        return $this;
    }

    public function isInheritedTwitterDescription(): ?bool
    {
        return $this->isInheritedTwitterDescription;
    }

    public function setIsInheritedTwitterDescription(?bool $isInheritedTwitterDescription): SeoDataFetchResultStruct
    {
        $this->isInheritedTwitterDescription = $isInheritedTwitterDescription;
        return $this;
    }

    public function getTwitterImage(): ?string
    {
        return $this->twitterImage;
    }

    public function setTwitterImage(?string $twitterImage): SeoDataFetchResultStruct
    {
        $this->twitterImage = $twitterImage;
        return $this;
    }


}
