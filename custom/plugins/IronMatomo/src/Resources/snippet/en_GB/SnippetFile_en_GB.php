<?php declare(strict_types=1);

namespace IronMatomo\Resources\snippet\en_GB;

use Shopware\Core\System\Snippet\Files\AbstractSnippetFile;

class SnippetFile_en_GB extends AbstractSnippetFile
{
    public function getName(): string
    {
        return 'IronMatomo.en-GB';
    }

    public function getPath(): string
    {
        return __DIR__ . '/IronMatomo.en-GB.json';
    }

    public function getIso(): string
    {
        return 'en-GB';
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
