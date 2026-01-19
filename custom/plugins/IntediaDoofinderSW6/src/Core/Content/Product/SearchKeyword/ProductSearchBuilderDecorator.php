<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Content\Product\SearchKeyword;

use Intedia\Doofinder\Doofinder\Api\Search;
use Intedia\Doofinder\Storefront\Subscriber\SearchSubscriber;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

class ProductSearchBuilderDecorator implements ProductSearchBuilderInterface
{
    /**
     * @var ProductSearchBuilderInterface
     */
    private $decorated;

    /**
     * @var SystemConfigService
     */
    protected $systemConfigService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Search
     */
    protected $searchApi;

    public function __construct(
        ProductSearchBuilderInterface $decorated,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger,
        Search $searchApi
    ) {
        $this->decorated           = $decorated;
        $this->systemConfigService = $systemConfigService;
        $this->logger              = $logger;
        $this->searchApi           = $searchApi;
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     * @param SalesChannelContext $context
     */
    public function build(Request $request, Criteria $criteria, SalesChannelContext $context): void
    {
        if ($this->systemConfigService->get('IntediaDoofinderSW6.config.doofinderEnabled', $context ? $context->getSalesChannel()->getId() : null) && $criteria->getTerm() === SearchSubscriber::IS_DOOFINDER_TERM) {
            $criteria->setTerm(null);
            return; // Is handled by subscriber
        }

        $this->decorated->build($request, $criteria, $context);
    }
}
