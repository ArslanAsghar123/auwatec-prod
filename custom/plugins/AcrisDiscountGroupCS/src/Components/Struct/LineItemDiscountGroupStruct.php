<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components\Struct;

use Shopware\Core\Framework\Struct\Struct;

class LineItemDiscountGroupStruct extends Struct
{
    private string $internalName;

    private float $discount;

    private ?string $discountType;

    private ?float $priority;

    private ?\DateTimeInterface $activeFrom;

    private ?\DateTimeInterface $activeUntil;


    public function __construct(string $internalName,
                                float $discount,
                                string $discountType,
                                float $priority,
                                \DateTimeInterface $activeFrom,
                                \DateTimeInterface $activeUntil )
    {
        $this->internalName = $internalName;
        $this->discount = $discount;
        $this->discountType = $discountType;
        $this->priority = $priority;
        $this->activeFrom = $activeFrom;
        $this->activeUntil = $activeUntil;
    }

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
    }

    /**
     * @param string $internalName
     */
    public function setInternalName(string $internalName): void
    {
        $this->internalName = $internalName;
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
     * @return float
     */
    public function getPriority(): float
    {
        return $this->priority;
    }

    /**
     * @param float $priority
     */
    public function setPriority(float $priority): void
    {
        $this->priority = $priority;
    }
}