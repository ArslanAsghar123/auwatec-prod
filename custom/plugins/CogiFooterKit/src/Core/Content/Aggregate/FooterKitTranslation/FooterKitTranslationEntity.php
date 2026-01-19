<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Core\Content\Aggregate\FooterKitTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Cogi\CogiFooterKit\Core\Content\FooterKitEntity;
use Shopware\Core\System\Language\LanguageEntity;

class FooterKitTranslationEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var array|null
     */
    protected $navigationBlock;

    /**
     * @var array|null
     */
    protected $informationBlock;

    /**
     * @var array|null
     */
    protected $customLink;

    /**
     * @var string|null
     */
    protected $socialMediaString;

    /**
     * @var string|null
     */
    protected $paymentString;

    /**
     * @var string|null
     */
    protected $shippingString;

    /**
     * @var string|null
     */
    protected $productSliderTitle;

    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt;

    /**
     * @var string
     */
    protected $cogiFooterKitId;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var FooterKitEntity|null
     */
    protected $cogiFooterKit;

    /**
     * @var LanguageEntity|null
     */
    protected $language;

    public function getNavigationBlock(): ?array
    {
        return $this->navigationBlock;
    }

    public function setNavigationBlock(?array $navigationBlock): void
    {
        $this->navigationBlock = $navigationBlock;
    }

    public function getInformationBlock(): ?array
    {
        return $this->informationBlock;
    }

    public function setInformationBlock(?array $informationBlock): void
    {
        $this->informationBlock = $informationBlock;
    }

    public function getCustomLink(): ?array
    {
        return $this->customLink;
    }

    public function setCustomLink(?array $customLink): void
    {
        $this->customLink = $customLink;
    }

    public function getSocialMediaString(): ?string
    {
        return $this->socialMediaString;
    }

    public function setSocialMediaString(?string $socialMediaString): void
    {
        $this->socialMediaString = $socialMediaString;
    }

    public function getPaymentString(): ?string
    {
        return $this->paymentString;
    }

    public function setPaymentString(?string $paymentString): void
    {
        $this->paymentString = $paymentString;
    }

    public function getShippingString(): ?string
    {
        return $this->shippingString;
    }

    public function setShippingString(?string $shippingString): void
    {
        $this->shippingString = $shippingString;
    }

    public function getProductSliderTitle(): ?string
    {
        return $this->productSliderTitle;
    }

    public function setProductSliderTitle(?string $productSliderTitle): void
    {
        $this->productSliderTitle = $productSliderTitle;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCogiFooterKitId(): string
    {
        return $this->cogiFooterKitId;
    }

    public function setCogiFooterKitId(string $cogiFooterKitId): void
    {
        $this->cogiFooterKitId = $cogiFooterKitId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getCogiFooterKit(): ?FooterKitEntity
    {
        return $this->cogiFooterKit;
    }

    public function setCogiFooterKit(?FooterKitEntity $cogiFooterKit): void
    {
        $this->cogiFooterKit = $cogiFooterKit;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): void
    {
        $this->language = $language;
    }
}