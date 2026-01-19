<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\Category;

use DreiscSeoPro\Core\BulkGenerator\Message\BulkGeneratorMessageService;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use DreiscSeoPro\Core\BulkGenerator\CategoryGenerator;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\CategoryEvents;
use Shopware\Core\Content\Category\DataAbstractionLayer\CategoryIndexingMessage;
use Shopware\Core\Content\Product\DataAbstractionLayer\ProductIndexingMessage;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;

class CategoryIndexer extends EntityIndexer
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
        private readonly CategoryGenerator $categoryGenerator,
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
        return 'dreisc_seo.category.indexer';
    }

    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new CategoryIndexingMessage(array_values($ids), $iterator->getOffset());
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

        $categoryEvent = $event->getEventByEntityName(CategoryDefinition::ENTITY_NAME);
        if (null === $categoryEvent || CategoryEvents::CATEGORY_DELETED_EVENT === $categoryEvent->getName()) {
            return null;
        }

        foreach($categoryEvent->getIds() as $categoryId) {
            $this->bulkGeneratorMessageService->dispatchSingleBulkGenerator(
                $categoryId,
                DreiscSeoBulkEnum::AREA__CATEGORY
            );
        }

        return new ProductIndexingMessage(array_values($categoryEvent->getIds()), null, $event->getContext());
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
