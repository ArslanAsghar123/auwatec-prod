<?php declare(strict_types=1);

namespace DreiscSeoPro\Decorator\Shopware\Storefront\Routing;

use DreiscSeoPro\Core\Routing\RequestTransformer;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\RequestTransformerInterface;
use Shopware\Storefront\Framework\Routing\Exception\SalesChannelMappingException;
use Symfony\Component\HttpFoundation\Request;

class RequestTransformerDecorator implements RequestTransformerInterface
{
    public function __construct(private readonly RequestTransformerInterface $decorated, private readonly RequestTransformer $requestTransformer)
    {
    }

    /**
     * @param Request $request
     * @return Request
     * @throws SalesChannelMappingException
     * @throws InconsistentCriteriaIdsException
     */
    public function transform(Request $request): Request
    {
        /**
         * Start the shopware progress
         * In this step the sales channel and the seo url information will be fetch
         */
        $clone = $this->decorated->transform($request);
        
        /**
         * Start SEO Professional transformer
         */
        return $this->requestTransformer->transform($clone, $request);
    }

    /**
     * Return only attributes that should be inherited by subrequests
     */
    public function extractInheritableAttributes(Request $sourceRequest): array
    {
        /**
         * Start the shopware progress
         */
        return $this->decorated->extractInheritableAttributes($sourceRequest);
    }
}
