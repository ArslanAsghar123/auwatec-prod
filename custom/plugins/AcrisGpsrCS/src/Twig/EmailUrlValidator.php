<?php declare(strict_types=1);

namespace Acris\Gpsr\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class EmailUrlValidator extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_valid_email', [$this, 'isValidEmail']),
            new TwigFunction('is_valid_url', [$this, 'isValidUrl']),

        ];
    }

    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}