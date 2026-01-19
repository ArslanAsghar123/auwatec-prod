<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Logo;

interface LogoRichSnippetLdBuilderInterface
{
    /**
     * @param LogoRichSnippetLdBuilderStruct $logoRichSnippetLdBuilderStruct
     * @return array
     */
    public function build(LogoRichSnippetLdBuilderStruct $logoRichSnippetLdBuilderStruct): array;
}
