<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\LocalBusiness;

interface LocalBusinessRichSnippetLdBuilderInterface
{
    public function build(LocalBusinessRichSnippetLdBuilderStruct $localBusinessRichSnippetLdBuilderStruct): array;
}
