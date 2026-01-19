<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\DemoData;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidLengthException;
use Shopware\Core\Framework\Uuid\Uuid;

class DemoDataRepository
{
    private ?string $cachedLanguageIdEn = null;

    private ?string $cachedLanguageIdDe = null;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws InvalidUuidException
     * @throws InvalidUuidLengthException
     */
    public function getLanguageIdDe(): string
    {
        if (null !== $this->cachedLanguageIdDe) {
            return $this->cachedLanguageIdDe;
        }

        $this->cachedLanguageIdDe = $this->getLanguageIdByName('Deutsch');

        return $this->cachedLanguageIdDe;
    }

    /**
     * @throws InvalidUuidException
     * @throws InvalidUuidLengthException
     */
    public function getLanguageIdEn(): string
    {
        if (null !== $this->cachedLanguageIdEn) {
            return $this->cachedLanguageIdEn;
        }

        $this->cachedLanguageIdEn = $this->getLanguageIdByName('English');

        return $this->cachedLanguageIdEn;
    }

    /**
     * @throws InvalidUuidException
     * @throws InvalidUuidLengthException
     */
    private function getLanguageIdByName(string $name): string
    {
        $id = $this->connection->createQueryBuilder()
            ->from('language')
            ->select('id')
            ->where('name = :name')
            ->setParameter('name', $name)
            ->execute()->fetchColumn();

        if (false === $id) {
            throw new RuntimeException("The language named '" . $name . "' was not found");
        }

        return Uuid::fromBytesToHex((string) $id);
    }
}
