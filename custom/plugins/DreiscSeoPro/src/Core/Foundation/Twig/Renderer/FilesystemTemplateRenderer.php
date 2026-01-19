<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Twig\Renderer;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class FilesystemTemplateRenderer extends Renderer
{
    private ?FilesystemLoader $filesystemLoader = null;

    public function __construct(private readonly string $templateDir)
    {
    }

    public function getLoader(): LoaderInterface
    {
        if (null === $this->filesystemLoader) {
            $this->filesystemLoader = new FilesystemLoader($this->templateDir);
        }

        return $this->filesystemLoader;
    }

    /**
     * @param null $name
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render($name = null): string
    {
        if (null === $name) {
            throw new \RuntimeException('Please set the template name as the param $name');
        }

        return $this->getEnvironment()->render(
            $name,
            $this->getContext()
        );
    }
}
