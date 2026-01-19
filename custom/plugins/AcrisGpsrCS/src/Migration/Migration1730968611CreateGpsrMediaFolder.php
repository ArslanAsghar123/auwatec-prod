<?php declare(strict_types=1);

namespace Acris\Gpsr\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1730968611CreateGpsrMediaFolder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1730968611;
    }

    public function update(Connection $connection): void
    {
        $this->createDefaultMediaFolders($connection);
    }

    private function createDefaultMediaFolders(Connection $connection): void
    {
        $existingDefaultTable = $connection->executeStatement(
            'SELECT `id` FROM `media_default_folder` WHERE `entity` = ?', ['acris_gprs_product_download']);

        if(empty($existingDefaultTable)) {
            $defaultFolderId = Uuid::randomBytes();

            $queue = new MultiInsertQueryQueue($connection);
            $queue->addInsert('media_default_folder',
                [
                    'id' => $defaultFolderId,
                    'entity' => 'acris_gprs_product_download',
                    'created_at' => date(Defaults::STORAGE_DATE_TIME_FORMAT)
                ]
            );

            $queue->execute();

            $this->createDefaultFolder(
                $connection,
                $defaultFolderId,
                'GPSR Downloads'
            );
        }
    }

    private function createDefaultFolder(Connection $connection, string $defaultFolderId, string $folderName): void
    {
        $connection->transactional(function (Connection $connection) use ($defaultFolderId, $folderName): void {
            $configurationId = Uuid::randomBytes();
            $folderId = Uuid::randomBytes();
            $private = 0;
            $connection->executeStatement('
                INSERT INTO `media_folder_configuration` (`id`, `thumbnail_quality`, `create_thumbnails`, `private`, created_at)
                VALUES (:id, 80, 0, :private, :createdAt)
            ', [
                'id' => $configurationId,
                'createdAt' => date(Defaults::STORAGE_DATE_TIME_FORMAT),
                'private' => $private,
            ]);

            $connection->executeStatement('
                INSERT into `media_folder` (`id`, 
                                            `name`, 
                                            `default_folder_id`, 
                                            `media_folder_configuration_id`,
                                            `use_parent_configuration`, 
                                            `child_count`, 
                                            `created_at`)
                VALUES (:folderId, 
                        :folderName, 
                        :defaultFolderId, 
                        :configurationId, 
                        0, 
                        0,
                        :createdAt)
            ', [
                'folderId' => $folderId,
                'folderName' => $folderName,
                'defaultFolderId' => $defaultFolderId,
                'configurationId' => $configurationId,
                'createdAt' => date(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        });
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
