<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Dbl\PlainSqlUpdate;

class Category
{
    public function __construct(private readonly Common $commonPlainSqlUpdater)
    {
    }

    public function update(array $updates)
    {
        $this->commonPlainSqlUpdater->updateTranslations(
            'category',
            $updates
        );
    }
}
