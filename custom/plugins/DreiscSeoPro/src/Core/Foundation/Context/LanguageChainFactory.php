<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Context;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Foundation\Context\ContextFactory\Struct\ContextStruct;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;

class LanguageChainFactory
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Return the language chain for the given language id
     *
     * @param $languageId
     * @throws DBALException
     * @throws InvalidUuidException
     */
    public function getLanguageIdChain($languageId): array
    {
        return [
            $languageId,
            $this->getParentLanguageId($languageId),
            Defaults::LANGUAGE_SYSTEM,
        ];
    }

    /**
     * @throws DBALException
     * @throws InvalidUuidException
     */
    private function getParentLanguageId(string $languageId): ?string
    {
        $languageId = Uuid::fromHexToBytes($languageId);

        $result = $this->connection
            ->executeQuery('SELECT LOWER(HEX(parent_id)) FROM language WHERE id = :id', ['id' => $languageId])
            ->fetchOne();

        return $result ? (string) $result : null;
    }
}
