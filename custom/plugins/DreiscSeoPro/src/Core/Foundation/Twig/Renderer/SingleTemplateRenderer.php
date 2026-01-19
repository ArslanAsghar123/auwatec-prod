<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Twig\Renderer;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;

class SingleTemplateRenderer extends Renderer
{
    final const TEMPLATE_KEY = 'twigTemplate';

    private ?ArrayLoader $arrayLoader = null;

    public function __construct(private readonly string $template)
    {
    }

    public function getLoader(): LoaderInterface
    {
        if (null === $this->arrayLoader) {
            $this->arrayLoader = new ArrayLoader([
                self::TEMPLATE_KEY => $this->template
            ]);
        }

        return $this->arrayLoader;
    }

    /**
     * @param null $name
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render($name = null): string
    {
        return $this->getEnvironment()->render(
            self::TEMPLATE_KEY,
            $this->getContext()
        );
    }
}
