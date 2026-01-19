<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class RowExportResultStruct extends DefaultStruct
{
    /**
     * @var string
     */
    protected $active = '';

    /**
     * @var string
     */
    protected $httpStatusCode = false;

    /**
     * @var bool
     */
    protected $parameterForwarding = '';

    /**
     * @var string
     */
    protected $sourceProductNumber = '';

    /**
     * @var string
     */
    protected $sourceInternalUrl = '';

    /**
     * @var string
     */
    protected $sourceCategoryId = '';

    /**
     * @var string
     */
    protected $sourceProductId = '';

    /**
     * @var string
     */
    protected $sourceRestrictionDomains = '';

    /**
     * @var string
     */
    protected $targetProductNumber = '';

    /**
     * @var string
     */
    protected $targetInternalUrl = '';

    /**
     * @var string
     */
    protected $targetCategoryId = '';

    /**
     * @var string
     */
    protected $targetProductId = '';

    /**
     * @var string
     */
    protected $targetExternalUrl = '';

    /**
     * @var string
     */
    protected $targetDeviatingDomain = '';

    public function getActive(): string
    {
        return $this->active;
    }

    public function setActive(string $active): RowExportResultStruct
    {
        $this->active = $active;
        return $this;
    }

    public function getHttpStatusCode(): string
    {
        return $this->httpStatusCode;
    }

    public function setHttpStatusCode(string $httpStatusCode): RowExportResultStruct
    {
        $this->httpStatusCode = $httpStatusCode;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getParameterForwarding(): bool|string
    {
        return $this->parameterForwarding;
    }

    /**
     * @param bool|string $parameterForwarding
     * @return RowExportResultStruct
     */
    public function setParameterForwarding(bool|string $parameterForwarding): RowExportResultStruct
    {
        $this->parameterForwarding = $parameterForwarding;
        return $this;
    }

    public function getSourceProductNumber(): string
    {
        return $this->sourceProductNumber;
    }

    public function setSourceProductNumber(string $sourceProductNumber): RowExportResultStruct
    {
        $this->sourceProductNumber = $sourceProductNumber;
        return $this;
    }

    public function getSourceInternalUrl(): string
    {
        return $this->sourceInternalUrl;
    }

    public function setSourceInternalUrl(string $sourceInternalUrl): RowExportResultStruct
    {
        $this->sourceInternalUrl = $sourceInternalUrl;
        return $this;
    }

    public function getSourceCategoryId(): string
    {
        return $this->sourceCategoryId;
    }

    public function setSourceCategoryId(string $sourceCategoryId): RowExportResultStruct
    {
        $this->sourceCategoryId = $sourceCategoryId;
        return $this;
    }

    public function getSourceProductId(): string
    {
        return $this->sourceProductId;
    }

    public function setSourceProductId(string $sourceProductId): RowExportResultStruct
    {
        $this->sourceProductId = $sourceProductId;
        return $this;
    }

    public function getSourceRestrictionDomains(): string
    {
        return $this->sourceRestrictionDomains;
    }

    public function setSourceRestrictionDomains(string $sourceRestrictionDomains): RowExportResultStruct
    {
        $this->sourceRestrictionDomains = $sourceRestrictionDomains;
        return $this;
    }

    public function getTargetProductNumber(): string
    {
        return $this->targetProductNumber;
    }

    public function setTargetProductNumber(string $targetProductNumber): RowExportResultStruct
    {
        $this->targetProductNumber = $targetProductNumber;
        return $this;
    }

    public function getTargetInternalUrl(): string
    {
        return $this->targetInternalUrl;
    }

    public function setTargetInternalUrl(string $targetInternalUrl): RowExportResultStruct
    {
        $this->targetInternalUrl = $targetInternalUrl;
        return $this;
    }

    public function getTargetCategoryId(): string
    {
        return $this->targetCategoryId;
    }

    public function setTargetCategoryId(string $targetCategoryId): RowExportResultStruct
    {
        $this->targetCategoryId = $targetCategoryId;
        return $this;
    }

    public function getTargetProductId(): string
    {
        return $this->targetProductId;
    }

    public function setTargetProductId(string $targetProductId): RowExportResultStruct
    {
        $this->targetProductId = $targetProductId;
        return $this;
    }

    public function getTargetExternalUrl(): string
    {
        return $this->targetExternalUrl;
    }

    public function setTargetExternalUrl(string $targetExternalUrl): RowExportResultStruct
    {
        $this->targetExternalUrl = $targetExternalUrl;
        return $this;
    }

    public function getTargetDeviatingDomain(): string
    {
        return $this->targetDeviatingDomain;
    }

    public function setTargetDeviatingDomain(string $targetDeviatingDomain): RowExportResultStruct
    {
        $this->targetDeviatingDomain = $targetDeviatingDomain;
        return $this;
    }
}
