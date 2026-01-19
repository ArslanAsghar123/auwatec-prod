<?php declare(strict_types=1);

namespace Acris\Gpsr\Components\ProductGpsrInfo\Struct;

use Shopware\Core\Content\Media\MediaEntity;

class GpsrInfoNoteStruct extends GpsrInfoStruct
{
    public const NOTE_TYPE_WARNING = 'warning';
    public const NOTE_TYPE_SECURITY = 'security';
    public const NOTE_TYPE_INFORMATION = 'information';

    protected bool $isNote = true;
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
    protected $documents = [];

    public function isNote(): bool
    {
        return $this->isNote;
    }

    public function setIsNote(bool $isNote): void
    {
        $this->isNote = $isNote;
    }

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

    public function getDocumentUrls(): array
    {
        return $this->documents;
    }

    public function setDocumentsUrls(array $documents): void
    {
        $this->documents = $documents;
    }
}
