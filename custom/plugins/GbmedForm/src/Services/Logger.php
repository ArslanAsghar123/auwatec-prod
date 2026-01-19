<?php declare(strict_types=1);
/**
 * gb media
 * All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * The content of this file is proprietary and confidential.
 *
 * @category       Shopware
 * @package        Shopware_Plugins
 * @subpackage     GbmedForm
 * @copyright      Copyright (c) 2020, gb media
 * @license        proprietary
 * @author         Giuseppe Bottino
 * @link           http://www.gb-media.biz
 */

namespace Gbmed\Form\Services;

use Monolog\Level;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;

readonly class Logger
{
    public function __construct(
        private EntityRepository $logEntryRepository,
        private LoggerInterface $logger,
        private string $name
    ) {
    }

    public function info(string $message, array $context): void
    {
        $this->logger->info($message, $context);
        $this->logEntry(
            $message,
            Level::Info,
            $context
        );
    }

    public function warning(string $message, array $context): void
    {
        $this->logger->warning($message, $context);
        $this->logEntry(
            $message,
            Level::Warning,
            $context
        );
    }

    public function error(string $message, array $context): void
    {
        $this->logger->error($message, $context);
        $this->logEntry(
            $message,
            Level::Error,
            $context
        );
    }

    public function debug(string $message, array $context): void
    {
        $this->logger->debug($message, $context);
        $this->logEntry(
            $message,
            Level::Debug,
            $context
        );
    }

    public function logEntry(string $message, Level $level, array $context): void
    {
        $data = [
            'id' => Uuid::randomHex(),
            'message' => $message,
            'level' => $level->value,
            'channel' => $this->name,
            'context' => $this->getContext($context)
        ];

        try {
            $this->logEntryRepository->create([$data], Context::createDefaultContext());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $data);
        }
    }

    private function getContext(array $context): array
    {
        return array_merge([
            'plugin' => $this->name
        ], $context);
    }
}
