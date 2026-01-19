<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Seo;

use Cocur\Slugify\Slugify;

class SeoUrlSlugify
{
    final const DEFAULT_REGEX = '/[^A-Za-z0-9\/\-_\.~]+/';

    public function __construct(private readonly Slugify $slugify)
    {
    }

    /**
     * Converts the given string to an url
     */
    public function convert(string $string, bool $urlToLower = true): string
    {
        $convertedUrl = $this->slugify->slugify(
            $string,
            [
                'regexp' => self::DEFAULT_REGEX
            ]
        );

        /** Convert the string lowercase */
        if ($urlToLower) {
            $convertedUrl = strtolower($convertedUrl);
        }

        /** Run post filters */
        $convertedUrl = $this->runPostFilters($convertedUrl);

        return $convertedUrl;
    }

    private function runPostFilters(string $convertedUrl): string
    {
        /** If there are more than one minus character we reduce it to one minus */
        $convertedUrl = preg_replace('/-{2,}/m', '-', $convertedUrl);

        return $convertedUrl;
    }
}
