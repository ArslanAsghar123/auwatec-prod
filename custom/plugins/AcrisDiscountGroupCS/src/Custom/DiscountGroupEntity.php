<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom;

use Acris\DiscountGroup\Custom\Aggregate\DiscountGroupTranslationCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class DiscountGroupEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    /**
     * @var string|null
     */
    protected $internalName;

    /**
     * @var string|null
     */
    protected $internalId;

    /**
     * @var string|null
     */
    protected $productId;

    /**
     * @var ProductEntity|null
     */
    protected $product;

    /**
     * @var string|null
     */
    protected $customerId;

    /**
     * @var CustomerEntity|null
     */
    protected $customer;

    /**
     * @var bool|null
     */
    protected $active;

    /**
     * @var \DateTimeInterface|null
     */
    protected $activeFrom;

    /**
     * @var \DateTimeInterface|null
     */
    protected $activeUntil;

    /**
     * @var float|null
     */
    protected $priority;

    /**
     * @var float
     */
    protected $discount;

    /**
     * @var string|null
     */
    protected $discountType;

    /**
     * @var RuleCollection|null
     */
    protected $rules;

    /**
     * @var bool|null
     */
    protected $excluded;

    /**
     * @var ProductStreamCollection|null
     */
    protected $productStreams;

    /**
     * @var null|array
     */
    protected $productIds;

    /**
     * @var array|null
     */
    protected $productStreamIds;

    /**
     * @var array|null
     */
    protected $ruleIds;

    /**
     * @var string|null
     */
    protected $listPriceType;

    /**
     * @var string|null
     */
    protected $calculationBase;

    /**
     * @var string|null
     */
    protected $rrpTax;

    /**
     * @var string|null
     */
    protected $rrpTaxDisplay;

    /**
     * @var string|null
     */
    protected $calculationType;

    /**
     * @var string
     */
    protected $customerAssignmentType;

    /**
     * @var string
     */
    protected $productAssignmentType;

    /**
     * @var string|null
     */
    protected $materialGroup;

    /**
     * @var string|null
     */
    protected $discountGroup;

    /**
     * @var bool|null
     */
    protected $accountDisplay;

    /**
     * @var int|null
     */
    protected $minQuantity;

    /**
     * @var int|null
     */
    protected $maxQuantity;

    /**
     * @var DiscountGroupTranslationCollection|null
     */
    protected $translations;

    /**
     * @return string|null
     */
    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    /**
     * @param string|null $internalName
     */
    public function setInternalName(?string $internalName): void
    {
        $this->internalName = $internalName;
    }

    /**
     * @return string|null
     */
    public function getInternalId(): ?string
    {
        return $this->internalId;
    }

    /**
     * @param string|null $internalId
     */
    public function setInternalId(?string $internalId): void
    {
        $this->internalId = $internalId;
    }


    /**
     * @return string|null
     */
    public function getProductId(): ?string
    {
        return $this->productId;
    }

    /**
     * @param string|null $productId
     */
    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return ProductEntity|null
     */
    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity|null $product
     */
    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    /**
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * @param string|null $customerId
     */
    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return CustomerEntity|null
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * @param CustomerEntity|null $customer
     */
    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool|null $active
     */
    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getActiveFrom(): ?\DateTimeInterface
    {
        return $this->activeFrom;
    }

    /**
     * @param \DateTimeInterface|null $activeFrom
     */
    public function setActiveFrom(?\DateTimeInterface $activeFrom): void
    {
        $this->activeFrom = $activeFrom;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getActiveUntil(): ?\DateTimeInterface
    {
        return $this->activeUntil;
    }

    /**
     * @param \DateTimeInterface|null $activeUntil
     */
    public function setActiveUntil(?\DateTimeInterface $activeUntil): void
    {
        $this->activeUntil = $activeUntil;
    }

    /**
     * @return float|null
     */
    public function getPriority(): ?float
    {
        return $this->priority;
    }

    /**
     * @param float|null $priority
     */
    public function setPriority(?float $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return string|null
     */
    public function getDiscountType(): ?string
    {
        return $this->discountType;
    }

    /**
     * @param string|null $discountType
     */
    public function setDiscountType(?string $discountType): void
    {
        $this->discountType = $discountType;
    }

    /**
     * @return RuleCollection|null
     */
    public function getRules(): ?RuleCollection
    {
        return $this->rules;
    }

    /**
     * @param RuleCollection|null $rules
     */
    public function setRules(?RuleCollection $rules): void
    {
        $this->rules = $rules;
    }

    /**
     * @return ProductStreamCollection|null
     */
    public function getProductStreams(): ?ProductStreamCollection
    {
        return $this->productStreams;
    }

    /**
     * @param ProductStreamCollection|null $productStreams
     */
    public function setProductStreams(?ProductStreamCollection $productStreams): void
    {
        $this->productStreams = $productStreams;
    }

    /**
     * @return array|null
     */
    public function getProductIds(): ?array
    {
        return $this->productIds;
    }

    /**
     * @param array|null $productIds
     */
    public function setProductIds(?array $productIds): void
    {
        $this->productIds = $productIds;
    }

    /**
     * @return string|null
     */
    public function getListPriceType(): ?string
    {
        return $this->listPriceType;
    }

    /**
     * @param string|null $listPriceType
     */
    public function setListPriceType(?string $listPriceType): void
    {
        $this->listPriceType = $listPriceType;
    }

    /**
     * @return string|null
     */
    public function getCalculationBase(): ?string
    {
        return $this->calculationBase;
    }

    /**
     * @param string|null $calculationBase
     */
    public function setCalculationBase(?string $calculationBase): void
    {
        $this->calculationBase = $calculationBase;
    }

    /**
     * @return string|null
     */
    public function getRrpTax(): ?string
    {
        return $this->rrpTax;
    }

    /**
     * @param string|null $rrpTax
     */
    public function setRrpTax(?string $rrpTax): void
    {
        $this->rrpTax = $rrpTax;
    }

    /**
     * @return string|null
     */
    public function getCalculationType(): ?string
    {
        return $this->calculationType;
    }

    /**
     * @param string|null $calculationType
     */
    public function setCalculationType(?string $calculationType): void
    {
        $this->calculationType = $calculationType;
    }

    /**
     * @return string
     */
    public function getCustomerAssignmentType(): string
    {
        return $this->customerAssignmentType;
    }

    /**
     * @param string $customerAssignmentType
     */
    public function setCustomerAssignmentType(string $customerAssignmentType): void
    {
        $this->customerAssignmentType = $customerAssignmentType;
    }

    /**
     * @return string
     */
    public function getProductAssignmentType(): string
    {
        return $this->productAssignmentType;
    }

    /**
     * @param string $productAssignmentType
     */
    public function setProductAssignmentType(string $productAssignmentType): void
    {
        $this->productAssignmentType = $productAssignmentType;
    }

    /**
     * @return string|null
     */
    public function getMaterialGroup(): ?string
    {
        return $this->materialGroup;
    }

    /**
     * @param string|null $materialGroup
     */
    public function setMaterialGroup(?string $materialGroup): void
    {
        $this->materialGroup = $materialGroup;
    }

    /**
     * @return bool|null
     */
    public function getExcluded(): ?bool
    {
        return $this->excluded;
    }

    /**
     * @param bool|null $excluded
     */
    public function setExcluded(?bool $excluded): void
    {
        $this->excluded = $excluded;
    }

    /**
     * @return string|null
     */
    public function getDiscountGroup(): ?string
    {
        return $this->discountGroup;
    }

    /**
     * @param string|null $discountGroup
     */
    public function setDiscountGroup(?string $discountGroup): void
    {
        $this->discountGroup = $discountGroup;
    }

    /**
     * @return array|null
     */
    public function getProductStreamIds(): ?array
    {
        return $this->productStreamIds;
    }

    /**
     * @param array|null $productStreamIds
     */
    public function setProductStreamIds(?array $productStreamIds): void
    {
        $this->productStreamIds = $productStreamIds;
    }

    /**
     * @return array|null
     */
    public function getRuleIds(): ?array
    {
        return $this->ruleIds;
    }

    /**
     * @param array|null $ruleIds
     */
    public function setRuleIds(?array $ruleIds): void
    {
        $this->ruleIds = $ruleIds;
    }

    /**
     * @return bool|null
     */
    public function getAccountDisplay(): ?bool
    {
        return $this->accountDisplay;
    }

    /**
     * @param bool|null $accountDisplay
     */
    public function setAccountDisplay(?bool $accountDisplay): void
    {
        $this->accountDisplay = $accountDisplay;
    }

    /**
     * @return DiscountGroupTranslationCollection|null
     */
    public function getTranslations(): ?DiscountGroupTranslationCollection
    {
        return $this->translations;
    }

    /**
     * @param DiscountGroupTranslationCollection|null $translations
     */
    public function setTranslations(?DiscountGroupTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    /**
     * @return int|null
     */
    public function getMinQuantity(): ?int
    {
        return $this->minQuantity;
    }

    /**
     * @param int|null $minQuantity
     */
    public function setMinQuantity(?int $minQuantity): void
    {
        $this->minQuantity = $minQuantity;
    }

    /**
     * @return int|null
     */
    public function getMaxQuantity(): ?int
    {
        return $this->maxQuantity;
    }

    /**
     * @param int|null $maxQuantity
     */
    public function setMaxQuantity(?int $maxQuantity): void
    {
        $this->maxQuantity = $maxQuantity;
    }

    /**
     * @return string|null
     */
    public function getRrpTaxDisplay(): ?string
    {
        return $this->rrpTaxDisplay;
    }

    /**
     * @param string|null $rrpTaxDisplay
     */
    public function setRrpTaxDisplay(?string $rrpTaxDisplay): void
    {
        $this->rrpTaxDisplay = $rrpTaxDisplay;
    }
}
