<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator\Message;

use DreiscSeoPro\Core\BulkGenerator\CategoryGenerator;
use DreiscSeoPro\Core\BulkGenerator\ProductGenerator;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use RuntimeException;

class SingleBulkGeneratorMessageHandler
{
    public function __construct(
        private readonly ProductGenerator $productGenerator,
        private readonly CategoryGenerator $categoryGenerator,
    ) {}

    public function __invoke(SingleBulkGeneratorMessage $message)
    {
        if (DreiscSeoBulkEnum::AREA__PRODUCT === $message->getArea()) {
            $generator = $this->productGenerator;
        } elseif (DreiscSeoBulkEnum::AREA__CATEGORY === $message->getArea()) {
            $generator = $this->categoryGenerator;
        } else {
            throw new RuntimeException('Invalid area: ' . $message->getArea());
        }

        if(empty($message->getReferenceId())) {
            return;
        }

        $generator->generate(
            [ $message->getReferenceId() ],
            [ $message->getLanguageId() ],
            [ $message->getSeoOption() ],
            [ $message->getBulkGeneratorType() ],
        );
    }
}
