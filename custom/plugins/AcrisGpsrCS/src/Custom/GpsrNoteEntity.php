<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom;

use Acris\Gpsr\Custom\Aggregate\GpsrNoteTranslation\GpsrNoteTranslationCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;

class GpsrNoteEntity extends AbstractGpsrModuleEntity
{
    use EntityIdTrait;

    protected ?string $noteType = null;
    protected ?string $backgroundColor = null;
    protected ?string $borderColor = null;
    protected ?string $headlineColor = null;
    protected ?string $hintHeadlineSeoSize = null;
    protected ?string $hintAlignment = null;
    protected ?string $hintHeadlineColor = null;
    protected ?bool $hintEnableHeadlineSize = null;
    protected ?string $hintHeadlineSize = null;
    protected ?string $mediaPosition = null;
    protected ?int $mediaSize = null;
    protected ?string $mobileVisibility = null;
    protected ?string $mediaId = null;
    protected ?MediaEntity $media = null;
    protected ?string $parentId = null;

    public function getNoteType(): ?string
    {
        return $this->noteType;
    }

    public function setNoteType(?string $noteType): void
    {
        $this->noteType = $noteType;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(?string $borderColor): void
    {
        $this->borderColor = $borderColor;
    }

    public function getHeadlineColor(): ?string
    {
        return $this->headlineColor;
    }

    public function setHeadlineColor(?string $headlineColor): void
    {
        $this->headlineColor = $headlineColor;
    }

    public function getHintHeadlineSeoSize(): ?string
    {
        return $this->hintHeadlineSeoSize;
    }

    public function setHintHeadlineSeoSize(?string $hintHeadlineSeoSize): void
    {
        $this->hintHeadlineSeoSize = $hintHeadlineSeoSize;
    }

    public function getHintAlignment(): ?string
    {
        return $this->hintAlignment;
    }

    public function setHintAlignment(?string $hintAlignment): void
    {
        $this->hintAlignment = $hintAlignment;
    }

    public function getHintHeadlineColor(): ?string
    {
        return $this->hintHeadlineColor;
    }

    public function setHintHeadlineColor(?string $hintHeadlineColor): void
    {
        $this->hintHeadlineColor = $hintHeadlineColor;
    }

    public function getHintEnableHeadlineSize(): ?bool
    {
        return $this->hintEnableHeadlineSize;
    }

    public function setHintEnableHeadlineSize(?bool $hintEnableHeadlineSize): void
    {
        $this->hintEnableHeadlineSize = $hintEnableHeadlineSize;
    }

    public function getHintHeadlineSize(): ?string
    {
        return $this->hintHeadlineSize;
    }

    public function setHintHeadlineSize(?string $hintHeadlineSize): void
    {
        $this->hintHeadlineSize = $hintHeadlineSize;
    }

    public function getMediaPosition(): ?string
    {
        return $this->mediaPosition;
    }

    public function setMediaPosition(?string $mediaPosition): void
    {
        $this->mediaPosition = $mediaPosition;
    }

    public function getMediaSize(): ?int
    {
        return $this->mediaSize;
    }

    public function setMediaSize(?int $mediaSize): void
    {
        $this->mediaSize = $mediaSize;
    }

    public function getMobileVisibility(): ?string
    {
        return $this->mobileVisibility;
    }

    public function setMobileVisibility(?string $mobileVisibility): void
    {
        $this->mobileVisibility = $mobileVisibility;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }
}
