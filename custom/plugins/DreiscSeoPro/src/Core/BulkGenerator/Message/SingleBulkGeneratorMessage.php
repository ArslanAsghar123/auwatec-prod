<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\Message;

use DreiscSeoPro\Core\BulkGenerator\Message\BulkGeneratorMessage;
use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

class SingleBulkGeneratorMessage implements AsyncMessageInterface
{
    /**
     * @var string
     */
    protected $referenceId;

    /**
     * @var string
     */
    protected $area;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var string
     */
    protected $seoOption;

    /**
     * @var string
     */
    protected $bulkGeneratorType;

    /**
     * @param string $referenceId
     * @param string $area
     * @param string $languageId
     * @param string $seoOption
     * @param string $bulkGeneratorType
     */
    public function __construct(string $referenceId, string $area, string $languageId, string $seoOption, string $bulkGeneratorType)
    {
        $this->referenceId = $referenceId;
        $this->area = $area;
        $this->languageId = $languageId;
        $this->seoOption = $seoOption;
        $this->bulkGeneratorType = $bulkGeneratorType;
    }

    /**
     * @return string
     */
    public function getReferenceId(): string
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     * @return SingleBulkGeneratorMessage
     */
    public function setReferenceId(string $referenceId): SingleBulkGeneratorMessage
    {
        $this->referenceId = $referenceId;
        return $this;
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
     * @return SingleBulkGeneratorMessage
     */
    public function setArea(string $area): SingleBulkGeneratorMessage
    {
        $this->area = $area;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    /**
     * @param string $languageId
     * @return SingleBulkGeneratorMessage
     */
    public function setLanguageId(string $languageId): SingleBulkGeneratorMessage
    {
        $this->languageId = $languageId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSeoOption(): string
    {
        return $this->seoOption;
    }

    /**
     * @param string $seoOption
     * @return SingleBulkGeneratorMessage
     */
    public function setSeoOption(string $seoOption): SingleBulkGeneratorMessage
    {
        $this->seoOption = $seoOption;
        return $this;
    }

    /**
     * @return string
     */
    public function getBulkGeneratorType(): string
    {
        return $this->bulkGeneratorType;
    }

    /**
     * @param string $bulkGeneratorType
     * @return SingleBulkGeneratorMessage
     */
    public function setBulkGeneratorType(string $bulkGeneratorType): SingleBulkGeneratorMessage
    {
        $this->bulkGeneratorType = $bulkGeneratorType;
        return $this;
    }
}
