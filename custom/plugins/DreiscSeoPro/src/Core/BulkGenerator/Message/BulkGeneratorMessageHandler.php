<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\Message;

use DreiscSeoPro\Core\BulkGenerator\CategoryGenerator;
use DreiscSeoPro\Core\BulkGenerator\ProductGenerator;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Foundation\Dal\Iterator\IteratorFactory;
use DreiscSeoPro\Core\Foundation\Dal\Iterator\IteratorFactory\Struct\IteratorFactoryStruct;
use DreiscSeoPro\Core\Foundation\Dal\Iterator\IteratorFactory\Struct\OrderByStruct;
use RuntimeException;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Symfony\Component\Messenger\MessageBusInterface;

class BulkGeneratorMessageHandler
{
    public function __construct(
        private readonly IteratorFactory $iteratorFactory,
        private readonly CategoryGenerator $categoryGenerator,
        private readonly ProductGenerator $productGenerator,
        private readonly BulkGeneratorMessageService $bulkGeneratorMessageService,
    ) {}

    public function __invoke(BulkGeneratorMessage $message)
    {
        if (DreiscSeoBulkEnum::AREA__PRODUCT === $message->getArea()) {
            $generator = $this->productGenerator;
        } elseif (DreiscSeoBulkEnum::AREA__CATEGORY === $message->getArea()) {
            $generator = $this->categoryGenerator;
        } else {
            throw new RuntimeException('Invalid area: ' . $message->getArea());
        }

        $bulkIterator = $this->createBulkIterator($message->getArea(), $message->getOffset(), $message->getLimit());
        $ids = $bulkIterator->fetch();

        if(empty($ids)) {
            return;
        }

        $generator->generate($ids, $message->getLanguageIds(), $message->getSeoOptions(), $message->getBulkGeneratorTypes());
        $this->bulkGeneratorMessageService->continueBulkGenerator($message);
    }

    private function createBulkIterator(string $area, int $offset, int $limit)
    {
        switch ($area) {
            case DreiscSeoBulkEnum::AREA__CATEGORY:
                $entityDefinition = CategoryDefinition::class;
                $orderByStructs = [
                    new OrderByStruct('level'),
                    new OrderByStruct('auto_increment')
                ];
                break;

            case DreiscSeoBulkEnum::AREA__PRODUCT:
                $entityDefinition = ProductDefinition::class;
                $orderByStructs = [
                    new OrderByStruct('auto_increment')
                ];
                break;

            default:
                throw new RuntimeException('Invalid area: ' . $area);
        }

        return $this->iteratorFactory->createIdIterator(
            new IteratorFactoryStruct(
                $entityDefinition,
                $offset,
                $limit,
                $orderByStructs
            )
        );
    }
}
