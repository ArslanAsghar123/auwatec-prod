<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Core\Content;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Cogi\CogiFooterKit\Core\Content\Aggregate\FooterKitTranslation\FooterKitTranslationCollection;

class FooterKitEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $salesChannelId;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var array|null
     */
    protected $navigationConfig;

    /**
     * @var array|null
     */
    protected $informationConfig;

    /**
     * @var array|null
     */
    protected $paymentShippingConfig;

    /**
     * @var array|null
     */
    protected $bottomConfig;

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
     * @var SalesChannelEntity|null
     */
    protected $salesChannel;

    /**
     * @var FooterKitTranslationCollection
     */
    protected $translations;

    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt;

    /**
     * @var array|null
     */
    protected $translated;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getNavigationConfig(): ?array
    {
        return $this->navigationConfig;
    }

    public function setNavigationConfig(?array $navigationConfig): void
    {
        $this->navigationConfig = $navigationConfig;
    }

    public function getInformationConfig(): ?array
    {
        return $this->informationConfig;
    }

    public function setInformationConfig(?array $informationConfig): void
    {
        $this->informationConfig = $informationConfig;
    }

    public function getPaymentShippingConfig(): ?array
    {
        return $this->paymentShippingConfig;
    }

    public function setPaymentShippingConfig(?array $paymentShippingConfig): void
    {
        $this->paymentShippingConfig = $paymentShippingConfig;
    }

    public function getBottomConfig(): ?array
    {
        return $this->bottomConfig;
    }

    public function setBottomConfig(?array $bottomConfig): void
    {
        $this->bottomConfig = $bottomConfig;
    }

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

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    public function getTranslations(): FooterKitTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(FooterKitTranslationCollection $translations): void
    {
        $this->translations = $translations;
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

    public function getTranslated(): array
    {
        return $this->translated;
    }

    public function setTranslated(?array $translated): void
    {
        $this->translated = $translated;
    }
}