<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\TemplateVariablesFetcher;

use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct\TemplateGeneratorStruct;
use DreiscSeoPro\Core\Content\Language\LanguageRepository;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\System\Language\LanguageEntity;

class CommonTemplateVariablesFetcher
{
    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    /**
     * @param LanguageRepository $languageRepository
     */
    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function fetch(array $variables, TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext, string $template): array
    {
        /** Fetch the language entity */
        $variables['language'] = $this->getLanguageEntity($templateGeneratorStruct, $languageChainContext, $template);
        $variables['systemDefaults'] = $this->getSystemDefaults();

        return $variables;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function getLanguageEntity(TemplateGeneratorStruct $templateGeneratorStruct, Context $languageChainContext, string $template): LanguageEntity
    {
        return $this->languageRepository->getCached(
            $templateGeneratorStruct->getLanguageId(),
            $this->getLanguageAssociationsByTemplate($template),
            $languageChainContext
        );
    }

    private function getLanguageAssociationsByTemplate(string $template): ?array
    {
        $associations = [];

        /** Load the locale association, if necessary */
        if(str_contains($template, 'language.locale')) {
            $associations[] = 'locale';
        }

        return !empty($associations) ? $associations : null;
    }

    /**
     * @return array
     */
    private function getSystemDefaults()
    {
        return [
            'LANGUAGE_SYSTEM' => Defaults::LANGUAGE_SYSTEM,
            'CURRENCY' => Defaults::CURRENCY
        ];
    }
}
