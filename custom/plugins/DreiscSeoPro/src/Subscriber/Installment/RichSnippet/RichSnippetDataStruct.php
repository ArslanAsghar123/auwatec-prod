<?php declare(strict_types=1);

namespace DreiscSeoPro\Subscriber\Installment\RichSnippet;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class RichSnippetDataStruct extends DefaultStruct
{
    /**
     * @var array
     */
    protected $ldJson;

    public function __construct(array $ldJson)
    {
        /** Filter empty or null entries */
        $ldJson = array_values(array_filter($ldJson));

        $this->ldJson = $ldJson;
    }

    public function getLdJson(): array
    {
        return $this->ldJson;
    }

    public function setLdJson(array $ldJson): RichSnippetDataStruct
    {
        $this->ldJson = $ldJson;

        return $this;
    }
}
