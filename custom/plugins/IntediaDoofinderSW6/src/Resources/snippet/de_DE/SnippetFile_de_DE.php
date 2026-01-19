<?php declare(strict_types=1);

namespace Intedia\Doofinder\Resources\snippet\de_DE;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_de_DE implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'doofinder.de-DE';
    }

    public function getPath(): string
    {
        return __DIR__ . '/doofinder.de-DE.json';
    }

    public function getIso(): string
    {
        return 'de-DE';
    }

    public function getAuthor(): string
    {
        return 'intedia GmbH';
    }

    public function isBase(): bool
    {
        return false;
    }
}