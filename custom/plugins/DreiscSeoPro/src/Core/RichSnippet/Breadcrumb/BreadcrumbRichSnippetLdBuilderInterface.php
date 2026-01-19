<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Breadcrumb;

interface BreadcrumbRichSnippetLdBuilderInterface
{
    /**
     * @param BreadcrumbRichSnippetLdBuilderStruct $breadcrumbRichSnippetLdBuilderStruct
     * @return array
     */
    public function build(BreadcrumbRichSnippetLdBuilderStruct $breadcrumbRichSnippetLdBuilderStruct): ?array;
}
