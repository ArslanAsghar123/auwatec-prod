<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Dbl\PlainSqlUpdate;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class Common
{
    public function __construct(private readonly Connection $connection, private readonly CacheClearer $cacheClearer)
    {
    }

    public function updateTranslations(string $entityName, array $updates)
    {
        $clearCacheIds = [];

        foreach($updates as $update) {
            $clearCacheIds[] = $this->updateTranslation($entityName, $update);
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function updateTranslation(string $entityName, array $update): ?string
    {
        $itemId = $update['id'];
        $translations = $update['translations'];
        $context = Context::createDefaultContext();

        if (empty($itemId) || empty($translations)) {
            return null;
        }

        foreach ($translations as $languageId => $translationFields) {
            $updateFields = [];
            $insertFields = [
                $entityName . '_id' => Uuid::fromHexToBytes($itemId),
                $entityName. '_version_id' => Uuid::fromHexToBytes($context->getVersionId()),
                'language_id' => Uuid::fromHexToBytes($languageId)
            ];

            if (!empty($translationFields['metaTitle'])) {
                $updateFields['meta_title'] = $translationFields['metaTitle'];
            }

            if (!empty($translationFields['metaDescription'])) {
                $updateFields['meta_description'] = $translationFields['metaDescription'];
            }

            if (empty($updateFields)) {
                continue;
            }

            $insertFields = array_merge($insertFields, $updateFields);
            $insertFieldNames = preg_filter('/$/', '`', preg_filter('/^/', '`', array_keys($insertFields)));
            $insertParamFields = preg_filter('/^/', ':', array_keys($insertFields));

            $updateString = [];
            foreach (array_keys($updateFields) as $updateField) {
                $updateString[] = sprintf('`%s` = :%s', $updateField, $updateField);
            }

            $sql = '
                INSERT INTO `' . $entityName . '_translation` (' . implode(', ', $insertFieldNames) . ', `created_at`)
                VALUES (' . implode(', ', $insertParamFields) . ', DATE(NOW()))
                ON DUPLICATE KEY UPDATE ' . implode(', ', $updateString) . '
            ';

            $update = $this->connection->prepare($sql);
            $update->execute($insertFields);
        }

        foreach($translations as $languageId => $translationFields) {
            $insertFields = [
                $entityName . '_id' => Uuid::fromHexToBytes($itemId),
                $entityName . '_version_id' => Uuid::fromHexToBytes($context->getVersionId()),
                'language_id' => Uuid::fromHexToBytes($languageId)
            ];

            if(empty($translationFields['customFields'])) {
                continue;
            }

            $customFields = $translationFields['customFields'];
            foreach($customFields as $customFieldKey => $customFieldValue) {
                $insertFieldNames = preg_filter('/$/', '`', preg_filter('/^/', '`', array_keys($insertFields)));
                $insertParamFields = preg_filter('/^/', ':', array_keys($insertFields));

                $jsonString = sprintf(
                    'JSON_SET(IFNULL(custom_fields, "{}"), "$.%s", %s)',
                    $customFieldKey,
                    $this->connection->quote($customFieldValue)
                );

                $sql = '
                    INSERT INTO `' . $entityName . '_translation` (' . implode(', ', $insertFieldNames) . ', `custom_fields`, `created_at`)
                    VALUES (' . implode(', ', $insertParamFields) . ', ' . $jsonString . ', DATE(NOW()))
                    ON DUPLICATE KEY UPDATE custom_fields = ' . $jsonString . '
                ';

                $update = $this->connection->prepare($sql);
                $update->execute($insertFields);
            }
        }

        return $itemId;
    }
}
