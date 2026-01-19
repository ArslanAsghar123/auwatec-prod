<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Framework\Adapter\Twig\Extension;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ThumbnailWidthExtension extends AbstractExtension
{
    public function __construct()
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('thumbnailWidth', [$this, 'thumbnailWidth']),
        ];
    }

    public function thumbnailWidth($thumbnails, $minWidth = 300, $maxWidth = 900)
    {
        /** @var MediaThumbnailEntity $thumbnail */
        foreach ($thumbnails as $thumbnail) {
            $width = $thumbnail->getWidth();
            if ($width >= $minWidth && $width <= $maxWidth) {
                return $thumbnail->getUrl();
            }
        }
    }
}
