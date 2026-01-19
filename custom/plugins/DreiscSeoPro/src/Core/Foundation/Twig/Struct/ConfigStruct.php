<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Twig\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class ConfigStruct extends DefaultStruct
{
    /**
     * @var bool
     */
    protected $strictVariablesEnabled = true;

    /**
     * @var bool
     */
    protected $debugModeEnabled = false;

    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var array
     */
    protected $twigFilters = [
        'lcfirst',
        'ucfirst',
        'rtrim',
        'ltrim',
        'count'
    ];

    public function isStrictVariablesEnabled(): bool
    {
        return $this->strictVariablesEnabled;
    }

    public function setStrictVariablesEnabled(bool $strictVariablesEnabled): ConfigStruct
    {
        $this->strictVariablesEnabled = $strictVariablesEnabled;

        return $this;
    }

    public function isDebugModeEnabled(): bool
    {
        return $this->debugModeEnabled;
    }

    public function setDebugModeEnabled(bool $debugModeEnabled): ConfigStruct
    {
        $this->debugModeEnabled = $debugModeEnabled;

        return $this;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    public function setCacheEnabled(bool $cacheEnabled): ConfigStruct
    {
        $this->cacheEnabled = $cacheEnabled;

        return $this;
    }

    public function getTwigFilters(): array
    {
        return $this->twigFilters;
    }

    public function setTwigFilters(array $twigFilters): ConfigStruct
    {
        $this->twigFilters = $twigFilters;

        return $this;
    }

    public function addTwigFilter(string $functionName): ConfigStruct
    {
        if (!in_array($functionName, $this->twigFilters)) {
            $this->twigFilters[] = $functionName;
        }

        return $this;
    }
}
