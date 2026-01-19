<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Components\Struct;

use Shopware\Core\Framework\Struct\Struct;

class LineItemDiscountGroupData extends Struct
{
    private ?string $discountGroupId;

    private ?string $discountGroupName;

    private ?int $discountGroupQuantity;

    public function __construct(
        ?string $discountGroupId,
        ?string $discountGroupName,
        ?int $discountGroupQuantity
    )
    {
        $this->discountGroupId = $discountGroupId;
        $this->discountGroupName =  $discountGroupName;
        $this->discountGroupQuantity =  $discountGroupQuantity;
    }

    /**
     * @return string|null
     */
    public function getDiscountGroupId(): ?string
    {
        return $this->discountGroupId;
    }

    /**
     * @param string|null $discountGroupId
     */
    public function setDiscountGroupId(?string $discountGroupId): void
    {
        $this->discountGroupId = $discountGroupId;
    }

    /**
     * @return string|null
     */
    public function getDiscountGroupName(): ?string
    {
        return $this->discountGroupName;
    }

    /**
     * @param string|null $discountGroupName
     */
    public function setDiscountGroupName(?string $discountGroupName): void
    {
        $this->discountGroupName = $discountGroupName;
    }

    /**
     * @return int|null
     */
    public function getDiscountGroupQuantity(): ?int
    {
        return $this->discountGroupQuantity;
    }

    /**
     * @param int|null $discountGroupQuantity
     */
    public function setDiscountGroupQuantity(?int $discountGroupQuantity): void
    {
        $this->discountGroupQuantity = $discountGroupQuantity;
    }
}