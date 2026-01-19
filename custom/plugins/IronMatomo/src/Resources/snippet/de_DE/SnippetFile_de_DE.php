<?php declare(strict_types=1);

namespace IronMatomo\Resources\snippet\de_DE;

use Shopware\Core\System\Snippet\Files\AbstractSnippetFile;

class SnippetFile_de_DE extends AbstractSnippetFile
{
    public function getName(): string
    {
        return 'IronMatomo.de-DE';
    }

    public function getPath(): string
    {
        return __DIR__ . '/IronMatomo.de-DE.json';
    }

    public function getIso(): string
    {
        return 'de-DE';
    }

    public function getAuthor(): string
    {
        return 'Martin Eisenführer';
    }

    public function isBase(): bool
    {
        return false;
    }

    public function getTechnicalName(): string
    {
        return 'IronMatomo';
    }
}
