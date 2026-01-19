<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\BaseInformation;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class BaseInformationDataStruct extends DefaultStruct
{
    /**
     * @var string|null
     */
    protected $navigationId;

    public function __construct(?string $navigationId)
    {
        $this->navigationId = $navigationId;
    }

    public function getNavigationId(): ?string
    {
        return $this->navigationId;
    }

    public function setNavigationId(?string $navigationId): BaseInformationDataStruct
    {
        $this->navigationId = $navigationId;

        return $this;
    }
}
