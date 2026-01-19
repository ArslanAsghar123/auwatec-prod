<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\TemplateGenerator;

use DreiscSeoPro\Core\Foundation\Context\ContextFactory\Struct\ContextStruct;
use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct\TemplateGeneratorStruct;
use DreiscSeoPro\Core\Foundation\Context\ContextFactory;
use DreiscSeoPro\Core\Foundation\Context\LanguageChainFactory;
use DreiscSeoPro\Core\Foundation\Twig\Renderer\SingleTemplateRenderer;
use DreiscSeoPro\Core\Foundation\Twig\Struct\ConfigStruct;
use DreiscSeoPro\Core\Foundation\Twig\TemplateRendererFactory;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;

class TemplateGeneratorHelper
{
    private array $cachedLanguageChains;

    public function __construct(private readonly ContextFactory $contextFactory, private readonly TemplateRendererFactory $templateRendererFactory, private readonly LanguageChainFactory $languageChainFactory)
    {
        $this->cachedLanguageChains = [];
    }

    /**
     * @param TemplateGeneratorStruct $bulkGeneratorStruct
     * @throws DBALException
     * @throws InvalidUuidException
     */
    public function createLanguageChainContext(string $languageId): Context
    {
        if(empty($this->cachedLanguageChains[$languageId])) {
            $languageChain = $this->languageChainFactory->getLanguageIdChain($languageId);
            $this->cachedLanguageChains[$languageId] = $this->contextFactory->createContext(
                (new ContextStruct())
                    ->setLanguageIdChain($languageChain)
            );
        }

        return $this->cachedLanguageChains[$languageId];
    }

    public function renderTemplate(string $template, array $variables, bool $spaceless): string
    {
        /** Wrap the template with the spaceless filter, if is option is active and there is no debug action */
        if (true === $spaceless && !str_contains($template, 'dump(')) {
            /** Add the spaceless filter */
            $template = '{% apply spaceless %}' . $template . '{% endapply %}';
        }

        $twig = $this->templateRendererFactory->createTwigEnvironment(
            new SingleTemplateRenderer($template),
            $variables,
            (new ConfigStruct())->setDebugModeEnabled(true)
        );

        return $twig->render();
    }
}
