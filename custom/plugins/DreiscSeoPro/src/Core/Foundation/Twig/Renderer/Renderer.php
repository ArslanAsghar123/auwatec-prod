<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Twig\Renderer;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;
use Twig\TemplateWrapper;

abstract class Renderer implements RendererInterface
{
    private ?Environment $environment = null;

    private ?array $context = null;

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * @return Renderer
     */
    public function setEnvironment(Environment $environment): RendererInterface
    {
        $this->environment = $environment;

        return $this;
    }

    public function setContext(array $context): RendererInterface
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
