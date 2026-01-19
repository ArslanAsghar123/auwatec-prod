<?php declare(strict_types=1);

namespace Weedesign\Images2WebP\Resources\snippet\de_DE;

class SnippetFile_de_DE 
{
    public function getName(): string
    {
        return 'storefront.de-DE';
    }

    public function getPath(): string
    {
        return __DIR__ . '/storefront.de-DE.json';
    }

    public function getIso(): string
    {
        return 'de-DE';
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
