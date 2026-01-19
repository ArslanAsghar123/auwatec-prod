<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\Message;

use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

class BulkGeneratorMessage implements AsyncMessageInterface
{
    /**
     * @var string
     */
    protected $area;

    /**
     * @var array
     */
    protected $languageIds;

    /**
     * @var array
     */
    protected $seoOptions;

    /**
     * @var array
     */
    protected $bulkGeneratorTypes;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @param string $area
     * @param array $languageIds
     * @param array $seoOptions
     * @param int $offset
     * @param int $limit
     * @param array $payload
     */
    public function __construct(string $area, array $languageIds, array $seoOptions, array $bulkGeneratorTypes, int $offset, int $limit, array $payload)
    {
        $this->area = $area;
        $this->languageIds = $languageIds;
        $this->seoOptions = $seoOptions;
        $this->bulkGeneratorTypes = $bulkGeneratorTypes;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * @param string $area
     * @return BulkGeneratorMessage
     */
    public function setArea(string $area): BulkGeneratorMessage
    {
        $this->area = $area;
        return $this;
    }

    /**
     * @return array
     */
    public function getLanguageIds(): array
    {
        return $this->languageIds;
    }

    /**
     * @param array $languageIds
     * @return BulkGeneratorMessage
     */
    public function setLanguageIds(array $languageIds): BulkGeneratorMessage
    {
        $this->languageIds = $languageIds;
        return $this;
    }

    /**
     * @return array
     */
    public function getSeoOptions(): array
    {
        return $this->seoOptions;
    }

    /**
     * @param array $seoOptions
     * @return BulkGeneratorMessage
     */
    public function setSeoOptions(array $seoOptions): BulkGeneratorMessage
    {
        $this->seoOptions = $seoOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getBulkGeneratorTypes(): array
    {
        return $this->bulkGeneratorTypes;
    }

    /**
     * @param array $bulkGeneratorTypes
     * @return BulkGeneratorMessage
     */
    public function setBulkGeneratorTypes(array $bulkGeneratorTypes): BulkGeneratorMessage
    {
        $this->bulkGeneratorTypes = $bulkGeneratorTypes;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return BulkGeneratorMessage
     */
    public function setOffset(int $offset): BulkGeneratorMessage
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return BulkGeneratorMessage
     */
    public function setLimit(int $limit): BulkGeneratorMessage
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     * @return BulkGeneratorMessage
     */
    public function setPayload(array $payload): BulkGeneratorMessage
    {
        $this->payload = $payload;
        return $this;
    }
}
