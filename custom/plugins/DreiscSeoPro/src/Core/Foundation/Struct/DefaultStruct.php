<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Struct;

use Shopware\Core\Framework\Struct\Struct;

class DefaultStruct extends Struct
{
    public function toArray()
    {
        $jsonArray = [];
        foreach (get_object_vars($this) as $key => $value) {
            $jsonArray[$key] = $value;
        }
        return $jsonArray;
    }
}
