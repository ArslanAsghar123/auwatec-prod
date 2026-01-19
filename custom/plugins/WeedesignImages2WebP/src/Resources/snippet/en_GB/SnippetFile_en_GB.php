<?php declare(strict_types=1);

namespace Weedesign\Images2WebP\Resources\snippet\en_GB;

class SnippetFile_en_GB
{
    public function getName(): string
    {
        return 'storefront.en-GB';
    }

    public function getPath(): string
    {
        return __DIR__ . '/storefront.en-GB.json';
    }

    public function getIso(): string
    {
        return 'en-GB';
    }

    public function getAuthor(): string
    {
        return 'weedesign';
    }

    public function isBase(): bool
    {
        return false;
    }
}
