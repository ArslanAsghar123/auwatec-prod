<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\SocialMedia;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class SocialMediaDataStruct extends DefaultStruct
{
    public function __construct(private ?string $facebookTitle, private ?string $facebookDescription, private ?string $facebookImage, private ?string $twitterTitle, private ?string $twitterDescription, private ?string $twitterImage)
    {
    }

    public function getFacebookTitle(): ?string
    {
        return $this->facebookTitle;
    }

    public function setFacebookTitle(?string $facebookTitle): SocialMediaDataStruct
    {
        $this->facebookTitle = $facebookTitle;
        return $this;
    }

    public function getFacebookDescription(): ?string
    {
        return $this->facebookDescription;
    }

    public function setFacebookDescription(?string $facebookDescription): SocialMediaDataStruct
    {
        $this->facebookDescription = $facebookDescription;
        return $this;
    }

    public function getFacebookImage(): ?string
    {
        return $this->facebookImage;
    }

    public function setFacebookImage(?string $facebookImage): SocialMediaDataStruct
    {
        $this->facebookImage = $facebookImage;
        return $this;
    }

    public function getTwitterTitle(): ?string
    {
        return $this->twitterTitle;
    }

    public function setTwitterTitle(?string $twitterTitle): SocialMediaDataStruct
    {
        $this->twitterTitle = $twitterTitle;
        return $this;
    }

    public function getTwitterDescription(): ?string
    {
        return $this->twitterDescription;
    }

    public function setTwitterDescription(?string $twitterDescription): SocialMediaDataStruct
    {
        $this->twitterDescription = $twitterDescription;
        return $this;
    }

    public function getTwitterImage(): ?string
    {
        return $this->twitterImage;
    }

    public function setTwitterImage(?string $twitterImage): SocialMediaDataStruct
    {
        $this->twitterImage = $twitterImage;
        return $this;
    }
}
