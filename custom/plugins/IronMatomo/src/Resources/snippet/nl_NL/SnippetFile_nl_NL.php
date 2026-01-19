<?php declare(strict_types=1);

namespace IronMatomo\Resources\snippet\nl_NL;

use Shopware\Core\System\Snippet\Files\AbstractSnippetFile;

class SnippetFile_nl_NL extends AbstractSnippetFile
{
    public function getName(): string
    {
        return 'IronMatomo.nl-NL';
    }

    public function getPath(): string
    {
        return __DIR__ . '/IronMatomo.nl-NL.json';
    }

    public function getIso(): string
    {
        return 'nl-NL';
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
