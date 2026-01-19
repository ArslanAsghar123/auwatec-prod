<?php declare(strict_types=1);

namespace Acris\Gpsr\Custom\Aggregate\ProductDownloadTranslation;

use Acris\Gpsr\Custom\ProductGpsrDownloadEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class ProductGpsrTranslationEntity extends TranslationEntity
{
    /**
     * @var string
     */
    protected $productDownloadId;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var ProductGpsrDownloadEntity
     */
    protected $productDownload;

    /**
     * @return string
     */
    public function getProductDownloadId(): string
    {
        return $this->productDownloadId;
    }

    /**
     * @param string $productDownloadId
     */
    public function setProductDownloadId(string $productDownloadId): void
    {
        $this->productDownloadId = $productDownloadId;
    }

    /**
     * @return ProductGpsrDownloadEntity
     */
    public function getProductDownload(): ProductGpsrDownloadEntity
    {
        return $this->productDownload;
    }

    /**
     * @param ProductGpsrDownloadEntity $productDownload
     */
    public function setProductDownload(ProductGpsrDownloadEntity $productDownload): void
    {
        $this->productDownload = $productDownload;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
