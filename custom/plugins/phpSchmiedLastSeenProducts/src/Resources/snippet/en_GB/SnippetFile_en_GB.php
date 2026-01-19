<?php declare(strict_types=1);

namespace phpSchmied\LastSeenProducts\Resources\snippet\en_GB;

use Shopware\Core\System\Snippet\Files\AbstractSnippetFile;
use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_en_GB extends AbstractSnippetFile
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
        return 'PHP-Schmiede';
    }

    public function isBase(): bool
    {
        return false;
    }

    public function getTechnicalName(): string
    {
        return 'PHP-Schmiede-Last seen products';
    }
}
