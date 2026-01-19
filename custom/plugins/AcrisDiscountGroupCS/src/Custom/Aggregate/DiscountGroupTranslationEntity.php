<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Custom\Aggregate;

use Acris\DiscountGroup\Custom\DiscountGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class DiscountGroupTranslationEntity extends TranslationEntity
{

    use EntityCustomFieldsTrait;

    /**
     * @var string|null
     */
    protected $displayText;

    /**
     * @var string|null
     */
    protected $displayName;

    /**
     * @var DiscountGroupEntity
     */
    protected $discountGroup;

    /**
     * @return string|null
     */
    public function getDisplayText(): ?string
    {
        return $this->displayText;
    }

    /**
     * @param string|null $displayText
     */
    public function setDisplayText(?string $displayText): void
    {
        $this->displayText = $displayText;
    }

    /**
     * @return DiscountGroupEntity
     */
    public function getDiscountGroup(): DiscountGroupEntity
    {
        return $this->discountGroup;
    }

    /**
     * @param DiscountGroupEntity $discountGroup
     */
    public function setDiscountGroup(DiscountGroupEntity $discountGroup): void
    {
        $this->discountGroup = $discountGroup;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     */
    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
    }
}
