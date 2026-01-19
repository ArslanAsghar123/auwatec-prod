<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Content\ProductExport\Service;

use Intedia\Doofinder\Core\Content\Settings\Service\SettingsHandler;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

/**
 * Class ExportHandler
 * @package Intedia\Doofinder\Core\Content\ProductExport\Service
 */
class ExportHandler
{
    /** @var Context $context */
    protected Context $context;

    /** @var EntityRepository */
    protected EntityRepository $productStreamRepository;

    /** @var EntityRepository */
    protected EntityRepository $salesChannelRepository;

    /** @var SettingsHandler $settingsHandler */
    protected SettingsHandler $settingsHandler;

    public function __construct(
        EntityRepository $productStreamRepository,
        EntityRepository $salesChannelRepository,
        SettingsHandler $settingsHandler
    ) {
        $this->settingsHandler         = $settingsHandler;
        $this->productStreamRepository = $productStreamRepository;
        $this->salesChannelRepository  = $salesChannelRepository;
    }


    public function getDooFinderStream(): ?ProductStreamEntity
    {
        $entities = $this->productStreamRepository->search($this->settingsHandler->getDooFinderStreamCriteria(), $this->settingsHandler->getContext());

        if ($entities->first() === null) {

            $this->productStreamRepository->upsert([$this->getDooFinderStreamData()], $this->settingsHandler->getContext());
            $entities = $this->productStreamRepository->search($this->settingsHandler->getDooFinderStreamCriteria(), $this->settingsHandler->getContext());
        }

        return $entities->first();
    }

    protected function getDooFinderStreamData(): array
    {
        return [
            'name' => 'DooFinder Produkte',
            'filters' => [
                [
                    'type' => 'multi',
                    'operator' => 'OR',
                    'queries' => [
                        [
                            'type' => 'multi',
                            'operator' => 'AND',
                            'queries' => [
                                [
                                    'type' => 'equals',
                                    'field' => 'active',
                                    'value' => '1'
                                ],
                                [
                                    'type' => 'multi',
                                    'operator' => 'OR',
                                    'queries' => [
                                        [
                                            'type' => 'range',
                                            'field' => 'stock',
                                            'parameters' => [
                                                'gt' => 0
                                            ]
                                        ],
                                        [
                                            'type' => 'equals',
                                            'field' => 'isCloseout',
                                            'value' => '0'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @throws \Exception
     */
    public function getSalesChannelAccessKey(): string
    {
        return 'IMDF' . strtoupper(str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(random_bytes(16))));
    }
}
