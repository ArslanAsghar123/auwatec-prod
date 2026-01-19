<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class TemplateGeneratorStruct extends DefaultStruct
{
    /**
     * @var string
     */
    protected $area;

    /**
     * @var string
     */
    protected $referenceId;

    /**
     * @var string
     */
    protected $seoOption;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var null|string
     */
    protected $salesChannelId;

    /**
     * @var bool
     */
    protected $spaceless;

    /**
     * @var null|string
     */
    protected $preferredCategoryId;

    /**
     * @var bool
     */
    protected $aiPrompt;

    public function __construct(string $area, string $referenceId, string $seoOption, string $languageId, ?string $salesChannelId, bool $spaceless, ?string $preferredCategoryId = null, bool $aiPrompt = false)
    {
        $this->area = $area;
        $this->referenceId = $referenceId;
        $this->seoOption = $seoOption;
        $this->languageId = $languageId;
        $this->salesChannelId = $salesChannelId;
        $this->spaceless = $spaceless;
        $this->preferredCategoryId = $preferredCategoryId;
        $this->aiPrompt = $aiPrompt;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * @return string|null
     */
    public function getpreferredCategoryId(): ?string
    {
        return $this->preferredCategoryId;
    }

    /**
     * @param string|null $preferredCategoryId
     * @return TemplateGeneratorStruct
     */
    public function setpreferredCategoryId(?string $preferredCategoryId): TemplateGeneratorStruct
    {
        $this->preferredCategoryId = $preferredCategoryId;

        return $this;
    }

    public function getReferenceId(): string
    {
        return $this->referenceId;
    }

    public function getSeoOption(): string
    {
        return $this->seoOption;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function isSpaceless(): bool
    {
        return $this->spaceless;
    }

    /**
     * @return bool
     */
    public function isAiPrompt(): bool
    {
        return $this->aiPrompt;
    }

    /**
     * @param bool $aiPrompt
     * @return TemplateGeneratorStruct
     */
    public function setAiPrompt(bool $aiPrompt): TemplateGeneratorStruct
    {
        $this->aiPrompt = $aiPrompt;
        return $this;
    }


}
