<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Routing;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;

class SourceSalesChannelDomainRestrictionChecker
{
    /**
     * Checks if the given redirect has a sales channel domain restriction
     */
    public function isValidRedirect(DreiscSeoRedirectEntity $dreiscSeoRedirectEntity, string $sourceSalesChannelDomainId): bool
    {
        /** It's valid, if no restriction is set */
        if (false === $dreiscSeoRedirectEntity->getHasSourceSalesChannelDomainRestriction()) {
            return true;
        }

        /** It's valid, if the given source channel domain is in the list */
        if (in_array($sourceSalesChannelDomainId, $dreiscSeoRedirectEntity->getSourceSalesChannelDomainRestrictionIds(), true)) {
            return true;
        }

        return false;
    }
}
