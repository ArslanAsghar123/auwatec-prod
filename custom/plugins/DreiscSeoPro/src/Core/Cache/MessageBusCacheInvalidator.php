<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Cache;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToRetrieveMetadata;

class MessageBusCacheInvalidator
{
    const ADMIN_CACHE_TIMESTAMP = 'seo_professional__admin_cache_timestamp';

    public function __construct(
        private readonly FilesystemOperator $fileSystemPrivate
    ) {}

    public function updateLastCacheTimestamp(): void
    {
        $this->fileSystemPrivate->write(self::ADMIN_CACHE_TIMESTAMP, (string) time());
    }

    public function isValid(int $storedDataCacheTimestamp): bool
    {
        try {
            $lastCacheTimestamp = (int) $this->fileSystemPrivate->read(self::ADMIN_CACHE_TIMESTAMP);
        } catch (\Exception $e) {
            return false;
        }

        return $lastCacheTimestamp < $storedDataCacheTimestamp;
    }
}
