<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\Message;

use Doctrine\DBAL\Connection;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkRepository;
use DreiscSeoPro\Core\Content\Language\LanguageRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Increment\IncrementGatewayRegistry;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

class BulkGeneratorMessageService
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LanguageRepository $languageRepository,
        private readonly Connection $connection,
        private readonly DreiscSeoBulkRepository $dreiscSeoBulkRepository,
        private readonly ServiceLocator $transportLocator,
        private readonly IncrementGatewayRegistry $incrementer
    ) {}

    public function dispatchSingleBulkGenerator(string $referenceId, string $area, ?array $languageIds = [], ?array $seoOptions = [], ?array $bulkGeneratorTypes = [], SymfonyStyle $io = null): void
    {
        $allLanguageIds = $this->getAllLanguageIds();
        $availableBulkSeoOptionsAndLanguages = $this->fetchAvailableBulkSeoOptionsAndLanguages($area);

        if(empty($languageIds)) {
            $languageIds = $allLanguageIds;
        }

        if(empty($seoOptions)) {
            $seoOptions = DreiscSeoBulkEnum::VALID_SEO_OPTIONS;
        }

        if(empty($bulkGeneratorTypes)) {
            $bulkGeneratorTypes = DreiscSeoBulkEnum::VALID_BULK_GENERATOR_TYPES;
        }

        foreach ($languageIds as $languageId) {
            if (!in_array($languageId, $allLanguageIds)) {
                throw new \InvalidArgumentException('Language id "' . $languageId . '" is not valid. Valid language ids are: ' . implode(', ', $allLanguageIds) . '.');
            }
        }

        foreach ($seoOptions as $seoOption) {
            if (!in_array($seoOption, DreiscSeoBulkEnum::VALID_SEO_OPTIONS)) {
                throw new \InvalidArgumentException('Seo option "' . $seoOption . '" is not valid. Valid seo options are: ' . implode(', ', DreiscSeoBulkEnum::VALID_SEO_OPTIONS) . '.');
            }
        }

        foreach ($bulkGeneratorTypes as $bulkGeneratorType) {
            if (!in_array($bulkGeneratorType, DreiscSeoBulkEnum::VALID_BULK_GENERATOR_TYPES)) {
                throw new \InvalidArgumentException('Bulk type "' . $bulkGeneratorType . '" is not valid. Valid bulk types are: ' . implode(', ', DreiscSeoBulkEnum::VALID_BULK_GENERATOR_TYPES) . '.');
            }
        }

        foreach($languageIds as $languageId) {
            foreach($seoOptions as $seoOption) {
                foreach($bulkGeneratorTypes as $bulkGeneratorType) {
                    if (empty($availableBulkSeoOptionsAndLanguages[$languageId][$seoOption][$bulkGeneratorType])) {
                        continue;
                    }

                    $singleBulkGeneratorMessage = new SingleBulkGeneratorMessage(
                        $referenceId,
                        $area,
                        $languageId,
                        $seoOption,
                        $bulkGeneratorType
                    );

                    $this->deleteOldSingleBulkGeneratorMessages($singleBulkGeneratorMessage);
                    $this->messageBus->dispatch($singleBulkGeneratorMessage);

                    $io?->success('Generating ' . $area . ' seo for reference id "' . $referenceId . '" and  language id "' . $languageId . '" and seo option "' . $seoOption . '" the bulk generator type "' . $bulkGeneratorType . '" started.');
                }
            }
        }

        $io?->info("The bulk generation is now processed via Shopware's message bus. Information about the progress can be found in the admin in the bulk generator module or in the database table \"messenger_messages\".");
    }

    public function dispatchBulkGenerator(string $area, ?array $languageIds = [], ?array $seoOptions = [], ?array $bulkGeneratorTypes = [], $limit = 25, SymfonyStyle $io = null): void
    {
        $allLanguageIds = $this->getAllLanguageIds();
        $availableBulkSeoOptionsAndLanguages = $this->fetchAvailableBulkSeoOptionsAndLanguages($area);

        if(empty($languageIds)) {
            $languageIds = $allLanguageIds;
        }

        if(empty($seoOptions)) {
            $seoOptions = DreiscSeoBulkEnum::VALID_SEO_OPTIONS;
        }

        if(empty($bulkGeneratorTypes)) {
            $bulkGeneratorTypes = DreiscSeoBulkEnum::VALID_BULK_GENERATOR_TYPES;
        }

        foreach ($languageIds as $languageId) {
            if (!in_array($languageId, $allLanguageIds)) {
                throw new \InvalidArgumentException('Language id "' . $languageId . '" is not valid. Valid language ids are: ' . implode(', ', $allLanguageIds) . '.');
            }
        }

        foreach ($seoOptions as $seoOption) {
            if (!in_array($seoOption, DreiscSeoBulkEnum::VALID_SEO_OPTIONS)) {
                throw new \InvalidArgumentException('Seo option "' . $seoOption . '" is not valid. Valid seo options are: ' . implode(', ', DreiscSeoBulkEnum::VALID_SEO_OPTIONS) . '.');
            }
        }

        foreach ($bulkGeneratorTypes as $bulkGeneratorType) {
            if (!in_array($bulkGeneratorType, DreiscSeoBulkEnum::VALID_BULK_GENERATOR_TYPES)) {
                throw new \InvalidArgumentException('Bulk type "' . $bulkGeneratorType . '" is not valid. Valid bulk types are: ' . implode(', ', DreiscSeoBulkEnum::VALID_BULK_GENERATOR_TYPES) . '.');
            }
        }

        $totalCount = $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from($area)
            ->executeQuery()
            ->fetchOne();

        $this->deleteOldBulkGeneratorMessages($area, $bulkGeneratorType, $io);

        foreach($languageIds as $languageId) {
            foreach($seoOptions as $seoOption) {
                foreach($bulkGeneratorTypes as $bulkGeneratorType) {
                    if (empty($availableBulkSeoOptionsAndLanguages[$languageId][$seoOption][$bulkGeneratorType])) {
                        continue;
                    }

                    $this->messageBus->dispatch(new BulkGeneratorMessage(
                        $area,
                        [ $languageId ],
                        [ $seoOption ],
                        [ $bulkGeneratorType ],
                        0,
                        empty($limit) ? 25 : $limit,
                        [
                            'messageUniqueId' => $this->createMessageUniqueId($area, [ $languageId ], [ $seoOption ], [ $bulkGeneratorType ]),
                            'totalCount' => $totalCount
                        ]
                    ));

                    $io?->success('Generating ' . $area . ' seo for language id "' . $languageId . '" and seo option "' . $seoOption . '" the bulk generator type "' . $bulkGeneratorType . '" started.');
                }
            }
        }

        $io?->info("The bulk generation is now processed via Shopware's message bus. Information about the progress can be found in the admin in the bulk generator module or in the database table \"messenger_messages\".");
    }

    public function continueBulkGenerator(BulkGeneratorMessage $message): void
    {
        $message->setOffset($message->getOffset() + $message->getLimit());
        $this->messageBus->dispatch($message);
    }

    public function getCurrentState(string $area)
    {
        $languageNames = [];
        $languageIdSearchResult = $this->languageRepository->search(new Criteria(), null, true);
        foreach($languageIdSearchResult->getEntities() as $language) {
            $languageNames[$language->getId()] = $language->getName();
        }

        $activeMessages = [];
        $totalCount = 0; $totalOffset = 0;

        $messengerMessages = $this->getMessages(BulkGeneratorMessage::class, /*true*/);
        if(isset($messengerMessages['incrementerCount'])) {
            return [
                'activeMessages' => 'incrementerCount',
                'totalCount' => (int) $messengerMessages['incrementerCount'],
                'totalOffset' => 0
            ];
        }

        $trackedMessages = [];
        foreach($messengerMessages as $messageKey => $messengerMessage) {
            $messengerBody = json_decode($messengerMessage['body'], true);
            $messengerHeaders = json_decode($messengerMessage['headers'], true);
            if (empty($messengerBody['payload']['totalCount'])) {
                continue;
            }

            if ($messengerBody['area'] !== $area) {
                /** Abort if the area does not match */
                continue;
            }

            $activeLanguageNames = [];
            foreach($messengerBody['languageIds'] as $languageId) {
                $activeLanguageNames[] = $languageNames[$languageId];
            }

            $exceptionMessage = null;
            $trace = null;
            if(!empty($messengerHeaders['X-Message-Stamp-Symfony\Component\Messenger\Stamp\ErrorDetailsStamp'])) {
                $error = json_decode($messengerHeaders['X-Message-Stamp-Symfony\Component\Messenger\Stamp\ErrorDetailsStamp'], true);

                if(!empty($error[0]['exceptionMessage'])) {
                    $exceptionMessage = $error[0]['exceptionMessage'];
                }

                if(!empty($error[0]['flattenException']['trace_as_string'])) {
                    $trace = $error[0]['flattenException']['trace_as_string'];
                }
            }

            $message = [
                'id' => $messengerMessage['id'],
                'languageIds' => $messengerBody['languageIds'],
                'activeLanguageNames' => implode(", ", $activeLanguageNames),
                'seoOptions' => implode(", ", $messengerBody['seoOptions']),
                'bulkGeneratorTypes' => implode(", ", $messengerBody['bulkGeneratorTypes']),
                'offset' => $messengerBody['offset'],
                'totalCount' => $messengerBody['payload']['totalCount'],
                'exceptionMessage' => $exceptionMessage,
                'trace' => $trace
            ];

            if(empty($trackedMessages[md5($message['activeLanguageNames'])][md5($message['seoOptions'])][md5($message['bulkGeneratorTypes'])])) {
                $trackedMessages[md5($message['activeLanguageNames'])][md5($message['seoOptions'])][md5($message['bulkGeneratorTypes'])] = true;

                $activeMessages[] = $message;
            }

            $totalCount += (int) $messengerBody['payload']['totalCount'];
            $totalOffset += (int) $messengerBody['offset'];
        }

        /** Sort $activeMessages first activeLanguageNames **/
        usort($activeMessages, function($a, $b) {
            return $a['activeLanguageNames'] <=> $b['activeLanguageNames'];
        });

        /** Sort after seoOptions */
        usort($activeMessages, function($a, $b) {
            return $a['seoOptions'] <=> $b['seoOptions'];
        });

        return [
            'activeMessages' => $activeMessages,
            'totalCount' => $totalCount,
            'totalOffset' => $totalOffset
        ];
    }

    /**
     * @param array $messengerMessages
     * @param mixed $messageUniqueId
     * @param SymfonyStyle|null $io
     * @param mixed $languageId
     * @param mixed $seoOption
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteOldBulkGeneratorMessages(string $area, string $bulkGeneratorType, ?SymfonyStyle $io): void
    {
        $messengerMessages = $this->getMessages(BulkGeneratorMessage::class);

        foreach ($messengerMessages as $messengerMessage) {
            $messengerBody = json_decode($messengerMessage['body'], true);

            if ($messengerBody['area'] !== $area) {
                /** Abort if the area does not match */
                continue;
            }

            $this->connection->executeStatement('DELETE FROM `messenger_messages` WHERE `id` = :id', ['id' => $messengerMessage['id']]);
        }
    }

    public function deleteOldSingleBulkGeneratorMessages(SingleBulkGeneratorMessage $singleBulkGeneratorMessage): void
    {
        $messengerMessages = $this->getMessages(SingleBulkGeneratorMessage::class);

        foreach ($messengerMessages as $messengerMessage) {
            $messengerBody = json_decode($messengerMessage['body'], true);

            if (
                empty($messengerBody['referenceId']) || $messengerBody['referenceId'] != $singleBulkGeneratorMessage->getReferenceId() ||
                empty($messengerBody['area']) || $messengerBody['area'] != $singleBulkGeneratorMessage->getArea() ||
                empty($messengerBody['languageId']) || $messengerBody['languageId'] != $singleBulkGeneratorMessage->getLanguageId() ||
                empty($messengerBody['seoOption']) || $messengerBody['seoOption'] != $singleBulkGeneratorMessage->getSeoOption() ||
                empty($messengerBody['bulkGeneratorType']) || $messengerBody['bulkGeneratorType'] != $singleBulkGeneratorMessage->getBulkGeneratorType()
            ) {
                continue;
            }

            $this->connection->executeStatement('DELETE FROM `messenger_messages` WHERE `id` = :id', ['id' => $messengerMessage['id']]);
        }
    }

    private function createMessageUniqueId(string $area, array $languageIds, array $seoOptions, array $bulkGeneratorTypes): string
    {
        return md5($area . implode('', $languageIds) . implode('', $seoOptions) . implode('', $bulkGeneratorTypes));
    }

    private function getMessages($messageClass, $lookIncrementer = false): array
    {
        $filteredMessengerMessages = [];
        $messengerMessages = $this->connection->fetchAllAssociative('SELECT * FROM `messenger_messages`');

        foreach ($messengerMessages as $messengerMessage) {
            $headers = json_decode($messengerMessage['headers'], true);
            if (empty($headers['type']) || $headers['type'] !== $messageClass) {
                continue;
            }

            $filteredMessengerMessages[] = $messengerMessage;
        }

        if(!empty($filteredMessengerMessages)) {
            return $filteredMessengerMessages;
        }

        if (!$lookIncrementer) {
            return [];
        }

        $incrementer = $this->incrementer->get(IncrementGatewayRegistry::MESSAGE_QUEUE_POOL);
        $list = $incrementer->list('message_queue_stats', -1);

        if(empty($list[BulkGeneratorMessage::class]) || empty($list[BulkGeneratorMessage::class]['count'])) {
            return [];
        }

        return [
            'incrementerCount' => $list[BulkGeneratorMessage::class]['count']
        ];
    }

    /**
     * @return array<string>
     */
    private function getTransportNames(): array
    {
        $transportNames = array_keys($this->transportLocator->getProvidedServices());

        return array_filter($transportNames, static fn(string $transportName) => str_starts_with($transportName, 'messenger.transport'));
    }

    private function fetchAvailableBulkSeoOptionsAndLanguages($area): array
    {
        $availableBulkSeoOptionsAndLanguages = [];
        $dreiscSeoBulkEntities = $this->dreiscSeoBulkRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('area', $area))
                ->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [
                    new EqualsFilter('dreiscSeoBulkTemplate.id', null)
                ]))
                ->addAssociation('dreiscSeoBulkTemplate')
        )->getEntities();
        if (null === $dreiscSeoBulkEntities) {
            return [];
        }

        foreach($dreiscSeoBulkEntities as $dreiscSeoBulkEntity) {
            $bulkGeneratorType = $dreiscSeoBulkEntity->getDreiscSeoBulkTemplate()->getAiPrompt() ?
                DreiscSeoBulkEnum::BULK_GENERATOR_TYPE__AI :
                DreiscSeoBulkEnum::BULK_GENERATOR_TYPE__DEFAULT;

            $availableBulkSeoOptionsAndLanguages[$dreiscSeoBulkEntity->getLanguageId()][$dreiscSeoBulkEntity->getSeoOption()][$bulkGeneratorType] = true;
        }

        return $availableBulkSeoOptionsAndLanguages;
    }

    private function getAllLanguageIds(): array
    {
        $languageIdSearchResult = $this->languageRepository->searchIds(new Criteria(), null, true);
        return $languageIdSearchResult->getIds();
    }
}
