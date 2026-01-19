<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\TemplateGenerator;

use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Exception\ReferenceEntityNotFoundException;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct\TemplateGeneratorStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;

interface TemplateGeneratorInterface
{
    /**
     * Generates and returns the template for the given setting
     *
     * @param TemplateGeneratorStruct $templateGeneratorStruct
     * @param string $template
     * @param Entity|null $translatedEntity
     * @param Context|null $context
     * @return string
     */
    public function generateTemplate(TemplateGeneratorStruct $templateGeneratorStruct, string $template, ?Entity $translatedEntity = null, Context $context = null): string;
}
