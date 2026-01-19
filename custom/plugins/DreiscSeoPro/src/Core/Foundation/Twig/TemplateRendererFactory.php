<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Twig;

use DreiscSeoPro\Core\Foundation\Twig\Renderer\RendererInterface;
use DreiscSeoPro\Core\Foundation\Twig\Struct\ConfigStruct;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\TwigFilter;

class TemplateRendererFactory
{
    /**
     * @param RendererInterface $renderer
     * @param ConfigStruct|null $configStruct
     * @return RendererInterface
     */
    public function createTwigEnvironment(RendererInterface $renderer, array $context = [], ConfigStruct $configStruct = null): RendererInterface
    {
        /** Creates a config struct, if not set */
        if (null === $configStruct) {
            $configStruct = new ConfigStruct();
        }

        /** Set the context */
        $renderer->setContext($context);

        /** Create a new twig instance */
        $renderer->setEnvironment(
            new Environment($renderer->getLoader(), [
                'debug' => $configStruct->isDebugModeEnabled(),
                'autoescape' => false
            ])
        );

        /** Activate the debug extension, if debug mode is on */
        if ($configStruct->isDebugModeEnabled()) {
            $renderer->getEnvironment()->addExtension(new DebugExtension());
        }

        /** Set twig config */
        $renderer->getEnvironment()->setCache($configStruct->isCacheEnabled());
        if ($configStruct->isStrictVariablesEnabled()) {
            $renderer->getEnvironment()->enableStrictVariables();
        }

        /** Set the twig filters */
        foreach($configStruct->getTwigFilters() as $twigFilter) {
            $renderer->getEnvironment()->addFilter(new TwigFilter($twigFilter, $twigFilter));
        }

        return $renderer;
    }
}
