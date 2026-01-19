<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class DreiscSeoRedirectImportExportFileEntity extends Entity
{
    use EntityIdTrait;

    final const ID__STORAGE_NAME = 'id';
    final const ID__PROPERTY_NAME = 'id';
    final const ORIGINAL_NAME__STORAGE_NAME = 'original_name';
    final const ORIGINAL_NAME__PROPERTY_NAME = 'originalName';
    final const PATH__STORAGE_NAME = 'path';
    final const PATH__PROPERTY_NAME = 'path';
    final const EXPIRE_DATE__STORAGE_NAME = 'expire_date';
    final const EXPIRE_DATE__PROPERTY_NAME = 'expireDate';
    final const SIZE__STORAGE_NAME = 'size';
    final const SIZE__PROPERTY_NAME = 'size';
    final const ACCESS_TOKEN__STORAGE_NAME = 'access_token';
    final const ACCESS_TOKEN__PROPERTY_NAME = 'accessToken';
    final const ACTIVITY__STORAGE_NAME = 'activity';
    final const ACTIVITY__PROPERTY_NAME = 'activity';
    final const STATE__STORAGE_NAME = 'state';
    final const STATE__PROPERTY_NAME = 'state';
    final const RECORDS__STORAGE_NAME = 'records';
    final const RECORDS__PROPERTY_NAME = 'records';
    final const CONFIG__STORAGE_NAME = 'config';
    final const CONFIG__PROPERTY_NAME = 'config';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $originalName;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \DateTime
     */
    protected $expireDate;

    /**
     * @var int|null
     */
    protected $size;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $activity;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var int
     */
    protected $records;

    /**
     * @var array
     */
    protected $config;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getExpireDate(): \DateTime
    {
        return $this->expireDate;
    }

    public function setExpireDate(\DateTime $expireDate): self
    {
        $this->expireDate = $expireDate;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getActivity(): string
    {
        return $this->activity;
    }

    public function setActivity(string $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getRecords(): int
    {
        return $this->records;
    }

    public function setRecords(int $records): self
    {
        $this->records = $records;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $jsonArray = [];
        foreach (get_object_vars($this) as $key => $value) {
            $jsonArray[$key] = $value;
        }

        return $jsonArray;
    }
}
