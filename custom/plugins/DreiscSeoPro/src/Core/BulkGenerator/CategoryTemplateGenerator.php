<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator;

use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Exception\ReferenceEntityNotFoundException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct\TemplateGeneratorStruct;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\TemplateGeneratorHelper;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\TemplateGeneratorInterface;
use DreiscSeoPro\Core\BulkGenerator\TemplateVariablesFetcher\CategoryTemplateVariablesFetcher;
use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Seo\SeoUrlSlugify;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Symfony\Component\Stopwatch\Stopwatch;

class CategoryTemplateGenerator implements TemplateGeneratorInterface
{
    public function __construct(private readonly TemplateGeneratorHelper $templateGeneratorHelper, private readonly CategoryTemplateVariablesFetcher $categoryTemplateVariablesFetcher, private readonly SeoUrlSlugify $seoUrlSlugify)
    {
    }

    /**
     * Generates and returns the template for the given setting
     *
     * @param Context|null $context
     */
    public function generateTemplate(TemplateGeneratorStruct $templateGeneratorStruct, string $template, ?Entity $translatedEntity = null, Context $context = null): string
    {
        if (null === $translatedEntity || null === $context) {
            /** Create a context for the selected language with the default language as fallback */
            $languageChainContext = $this->templateGeneratorHelper->createLanguageChainContext($templateGeneratorStruct->getLanguageId());

            /** Fetch the template variables */
            $variables = $this->categoryTemplateVariablesFetcher->fetchVariables(
                $templateGeneratorStruct,
                $languageChainContext,
                $template
            );
        } else {
            $variables = $this->categoryTemplateVariablesFetcher->fetchVariablesByTranslatedCategoryEntity(
                $translatedEntity,
                $templateGeneratorStruct,
                $context,
                $template
            );
        };

        /** Render the template */
        $renderedTemplate = $this->templateGeneratorHelper->renderTemplate(
            $template,
            $variables,
            $templateGeneratorStruct->isSpaceless()
        );

        /** Slugify the template result, if it's an url */
        if (!$templateGeneratorStruct->isAiPrompt() && DreiscSeoBulkEnum::SEO_OPTION__URL === $templateGeneratorStruct->getSeoOption()) {
            $renderedTemplate = $this->seoUrlSlugify->convert($renderedTemplate);
        }

        /** Return the template */
        return $renderedTemplate;
    }
}
