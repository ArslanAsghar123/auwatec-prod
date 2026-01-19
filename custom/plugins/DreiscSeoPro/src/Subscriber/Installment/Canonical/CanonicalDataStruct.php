<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\Canonical;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class CanonicalDataStruct extends DefaultStruct
{
    /**
     * @var string|null
     */
    protected $canonicalLink;

    public function __construct(?string $canonicalLink)
    {
        $this->canonicalLink = $canonicalLink;
    }

    public function getCanonicalLink(): ?string
    {
        return $this->canonicalLink;
    }

    public function setCanonicalLink(?string $canonicalLink): CanonicalDataStruct
    {
        $this->canonicalLink = $canonicalLink;

        return $this;
    }
}
