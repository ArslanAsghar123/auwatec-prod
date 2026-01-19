<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Product;

use DreiscSeoPro\Core\BulkGenerator\Message\BulkGeneratorMessageService;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use Shopware\Core\Content\Product\DataAbstractionLayer\ProductIndexingMessage;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;

class ProductIndexer extends EntityIndexer
{
    /**
     * @var bool
     */
    protected static $disabledBulkIndexer = false;

    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(
        private readonly IteratorFactory $iteratorFactory,
        EntityRepository $repository,
        private readonly CustomSettingLoader $customSettingLoader,
        private readonly BulkGeneratorMessageService $bulkGeneratorMessageService
    )
    {
        $this->repository = $repository;
    }

    /**
     * @param bool $disable
     */
    public static function disableBulkIndexer($disable = true) {
        self::$disabledBulkIndexer = $disable;
    }

    public function getName(): string
    {
        return 'dreisc_seo.product.indexer';
    }

    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new ProductIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    /**
     * The update method will be call by updating an entity
     *
     * @param EntityWrittenContainerEvent $event
     * @return EntityIndexingMessage|null
     */
    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $customSettingStruct = $this->customSettingLoader->load();
        $isStartGeneratorInTheStorageProcess = $customSettingStruct->getBulkGenerator()->getGeneral()->isStartGeneratorInTheStorageProcess();

        /** Abort if the bulk indexer is disabled or the bulk generator should not start */
        if (true === self::$disabledBulkIndexer || false === $isStartGeneratorInTheStorageProcess) {
            return null;
        }

        $productEvent = $event->getEventByEntityName(ProductDefinition::ENTITY_NAME);
        if (null === $productEvent || ProductEvents::PRODUCT_DELETED_EVENT === $productEvent->getName()) {
            return null;
        }

        foreach($productEvent->getIds() as $productId) {
            $this->bulkGeneratorMessageService->dispatchSingleBulkGenerator(
                $productId,
                DreiscSeoBulkEnum::AREA__PRODUCT
            );
        }

        return new ProductIndexingMessage(array_values($productEvent->getIds()), null, $event->getContext());
    }

    /**
     * The handle method will be call in the index process
     *
     * @param EntityIndexingMessage $message
     */
    public function handle(EntityIndexingMessage $message): void
    {
    }

    public function getTotal(): int
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition())->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(static::class);
    }
}
