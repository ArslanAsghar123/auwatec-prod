<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Context\ContextFactory\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;
use Shopware\Core\Framework\Api\Context\ContextSource;

class ContextStruct extends DefaultStruct
{
    /**
     * @var ContextSource|null
     */
    protected $contextSource = null;

    /**
     * @var array|null
     */
    protected $ruleIds = null;

    /**
     * @var string|null
     */
    protected $currencyId = null;

    /**
     * @var array|null
     */
    protected $languageIdChain = null;

    /**
     * @var string|null
     */
    protected $versionId = null;

    /**
     * @var float|null
     */
    protected $currencyFactor = null;

    /**
     * @var bool|null
     */
    protected $considerInheritance = null;

    /**
     * @var string|null
     */
    protected $taxState = null;

    public function getContextSource(): ?ContextSource
    {
        return $this->contextSource;
    }

    public function setContextSource(?ContextSource $contextSource): ContextStruct
    {
        $this->contextSource = $contextSource;
        return $this;
    }

    public function getRuleIds(): ?array
    {
        return $this->ruleIds;
    }

    public function setRuleIds(?array $ruleIds): ContextStruct
    {
        $this->ruleIds = $ruleIds;
        return $this;
    }

    public function getCurrencyId(): ?string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(?string $currencyId): ContextStruct
    {
        $this->currencyId = $currencyId;
        return $this;
    }

    public function getLanguageIdChain(): ?array
    {
        return $this->languageIdChain;
    }

    public function setLanguageIdChain(?array $languageIdChain): ContextStruct
    {
        $this->languageIdChain = $languageIdChain;
        return $this;
    }

    public function getVersionId(): ?string
    {
        return $this->versionId;
    }

    public function setVersionId(?string $versionId): ContextStruct
    {
        $this->versionId = $versionId;
        return $this;
    }

    public function getCurrencyFactor(): ?float
    {
        return $this->currencyFactor;
    }

    public function setCurrencyFactor(?float $currencyFactor): ContextStruct
    {
        $this->currencyFactor = $currencyFactor;
        return $this;
    }

    public function getConsiderInheritance(): ?bool
    {
        return $this->considerInheritance;
    }

    public function setConsiderInheritance(?bool $considerInheritance): ContextStruct
    {
        $this->considerInheritance = $considerInheritance;
        return $this;
    }

    public function getTaxState(): ?string
    {
        return $this->taxState;
    }

    public function setTaxState(?string $taxState): ContextStruct
    {
        $this->taxState = $taxState;
        return $this;
    }
}
