<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Framework\Adapter\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImCleanExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('imClean', [$this, 'imClean']),
        ];
    }

    public function imClean($string, $replacement = ' '): string
    {
        if ($string && is_string($string)) {
            return preg_replace('/[[:cntrl:]]/', $replacement, $string);
        }

        return '';
    }
}
