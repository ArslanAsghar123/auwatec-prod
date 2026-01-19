<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Twig\Renderer;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\LoaderInterface;
use Twig\TemplateWrapper;

interface RendererInterface
{
    /**
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface;

    /**
     * @param Environment $environment
     * @return RendererInterface
     */
    public function setEnvironment(Environment $environment): RendererInterface;

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment;

    /**
     * @param array $context
     * @return array
     */
    public function setContext(array $context): RendererInterface;

    /**
     * @return array
     */
    public function getContext(): array;

    /**
     * @param null $name
     * @return string
     */
    public function render($name = null): string;
}
