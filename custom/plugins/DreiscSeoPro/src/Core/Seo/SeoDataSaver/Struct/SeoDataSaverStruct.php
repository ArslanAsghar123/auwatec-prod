<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo\SeoDataSaver\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class SeoDataSaverStruct extends DefaultStruct
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
     * @var string|null
     */
    protected $salesChannelId;

    /**
     * @var string|null
     */
    protected $newValue;

    /**
     * @var string|null
     */
    protected $overwrite;

    /**
     * @var string|null
     */
    protected $overwriteCustomField;

    /**
     * @var bool|null
     */
    protected $overwriteCustomFieldValue;

    public function __construct(string $area, string $referenceId, string $seoOption, string $languageId, ?string $salesChannelId, ?string $overwrite, ?string $overwriteCustomField, ?bool $overwriteCustomFieldValue)
    {
        $this->area = $area;
        $this->referenceId = $referenceId;
        $this->seoOption = $seoOption;
        $this->languageId = $languageId;
        $this->salesChannelId = $salesChannelId;
        $this->overwrite = $overwrite;
        $this->overwriteCustomField = $overwriteCustomField;
        $this->overwriteCustomFieldValue = $overwriteCustomFieldValue;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function setArea(string $area): SeoDataSaverStruct
    {
        $this->area = $area;

        return $this;
    }

    public function getReferenceId(): string
    {
        return $this->referenceId;
    }

    public function setReferenceId(string $referenceId): SeoDataSaverStruct
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    public function getSeoOption(): string
    {
        return $this->seoOption;
    }

    public function setSeoOption(string $seoOption): SeoDataSaverStruct
    {
        $this->seoOption = $seoOption;

        return $this;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): SeoDataSaverStruct
    {
        $this->languageId = $languageId;

        return $this;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId): SeoDataSaverStruct
    {
        $this->salesChannelId = $salesChannelId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    /**
     * @param string|null $newValue
     * @return SeoDataSaverStruct
     */
    public function setNewValue(?string $newValue): SeoDataSaverStruct
    {
        $this->newValue = $newValue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOverwrite(): ?string
    {
        return $this->overwrite;
    }

    /**
     * @param string|null $overwrite
     * @return SeoDataSaverStruct
     */
    public function setOverwrite(?string $overwrite): SeoDataSaverStruct
    {
        $this->overwrite = $overwrite;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOverwriteCustomField(): ?string
    {
        return $this->overwriteCustomField;
    }

    /**
     * @param string|null $overwriteCustomField
     * @return SeoDataSaverStruct
     */
    public function setOverwriteCustomField(?string $overwriteCustomField): SeoDataSaverStruct
    {
        $this->overwriteCustomField = $overwriteCustomField;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getOverwriteCustomFieldValue(): ?bool
    {
        return $this->overwriteCustomFieldValue;
    }

    /**
     * @param bool|null $overwriteCustomFieldValue
     * @return SeoDataSaverStruct
     */
    public function setOverwriteCustomFieldValue(?bool $overwriteCustomFieldValue): SeoDataSaverStruct
    {
        $this->overwriteCustomFieldValue = $overwriteCustomFieldValue;
        return $this;
    }
}
