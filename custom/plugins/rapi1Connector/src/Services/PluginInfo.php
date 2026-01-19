<?php

declare(strict_types=1);

namespace Rapidmail\Shopware\Services;

class PluginInfo
{
    const PLUGIN_INFO_FILE = 'composer.json';

    public function getVersion(): ?string
    {
        try {
            $pluginInfo = json_decode(
                file_get_contents(dirname(__FILE__) . '/../../' . self::PLUGIN_INFO_FILE),
                true
            );

            return (isset($pluginInfo['version']) && !empty($pluginInfo['version'])) ? $pluginInfo['version'] : null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
