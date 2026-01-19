<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Context;

use DreiscSeoPro\Core\Foundation\Context\ContextFactory\Struct\ContextStruct;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;

class ContextFactory
{
    /**
     * Creates an context based on the default context
     */
    public function createContext(ContextStruct $contextStruct): Context
    {
        $defaultContext = Context::createDefaultContext();

        return new Context(
            $contextStruct->getContextSource() ?? new SystemSource(),
            $contextStruct->getRuleIds() ?? $defaultContext->getRuleIds(),
            $contextStruct->getCurrencyId() ?? $defaultContext->getCurrencyId(),
            $contextStruct->getLanguageIdChain() ?? $defaultContext->getLanguageIdChain(),
            $contextStruct->getVersionId() ?? $defaultContext->getVersionId(),
            $contextStruct->getCurrencyFactor() ?? $defaultContext->getCurrencyFactor(),
            $contextStruct->getConsiderInheritance() ?? $defaultContext->considerInheritance(),
            $contextStruct->getTaxState() ?? $defaultContext->getTaxState()
        );
    }
}
