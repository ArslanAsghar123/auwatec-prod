<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\RobotsTag;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class RobotsTagStruct extends DefaultStruct
{
    /**
     * @var string
     */
    protected $robotsTag;

    public function __construct(string $robotsTag)
    {
        $this->robotsTag = $robotsTag;
    }

    public function getRobotsTag(): string
    {
        return $this->robotsTag;
    }

    public function setRobotsTag(string $robotsTag): RobotsTagStruct
    {
        $this->robotsTag = $robotsTag;

        return $this;
    }
}
