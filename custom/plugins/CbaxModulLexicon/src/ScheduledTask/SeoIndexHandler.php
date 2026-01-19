<?php declare(strict_types=1);

namespace Cbax\ModulLexicon\ScheduledTask;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;

use Cbax\ModulLexicon\Components\LexiconSeo;

#[AsMessageHandler(handles: SeoIndex::class)]
class SeoIndexHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly LexiconSeo $lexiconSeo,
        protected readonly CacheClearer $cacheClearer
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $context = Context::createDefaultContext();
        $this->lexiconSeo->createSeoUrls($context, '');
    }
}
