<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Sitemap;

abstract class AbstractEntityFetcher
{
    protected function groupBySalesChannel($data): array
    {
        if(empty($data)) {
            return [];
        }

        try {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [];
        }

        if(!is_array($data)) {
            return [];
        }

        $rows = [];
        foreach ($data as $item) {
            $salesChannelId = $item['salesChannelId'];

            $rows[$salesChannelId] = $item;
        }

        return $rows;
    }

    protected function getTranslated($item, string $field, $defaultValue = null)
    {
        if (isset($item['customFields_currentLanguage'][$field]) && !empty($item['customFields_currentLanguage'][$field])) {
            return $item['customFields_currentLanguage'][$field];
        } elseif (isset($item['customFields_inheritLanguage'][$field]) && !empty($item['customFields_inheritLanguage'][$field])) {
            return $item['customFields_inheritLanguage'][$field];
        } elseif (isset($item['customFields_defaultLanguage'][$field]) && !empty($item['customFields_defaultLanguage'][$field])) {
            return $item['customFields_defaultLanguage'][$field];
        } else {
            return $defaultValue;
        }
    }
}
