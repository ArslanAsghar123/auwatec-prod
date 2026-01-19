<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportLog;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;

class DreiscSeoRedirectImportExportLogEntity extends Entity
{
    use EntityIdTrait;

    final const ID__STORAGE_NAME = 'id';
    final const ID__PROPERTY_NAME = 'id';
    final const DREISC_SEO_REDIRECT_ID__STORAGE_NAME = 'dreisc_seo_redirect_id';
    final const DREISC_SEO_REDIRECT_ID__PROPERTY_NAME = 'dreiscSeoRedirectId';
    final const ROW_INDEX__STORAGE_NAME = 'row_index';
    final const ROW_INDEX__PROPERTY_NAME = 'rowIndex';
    final const ROW_VALUE__STORAGE_NAME = 'row_value';
    final const ROW_VALUE__PROPERTY_NAME = 'rowValue';
    final const ERRORS__STORAGE_NAME = 'errors';
    final const ERRORS__PROPERTY_NAME = 'errors';
    final const DREISC_SEO_REDIRECT__STORAGE_NAME = 'dreisc_seo_redirect';
    final const DREISC_SEO_REDIRECT__PROPERTY_NAME = 'dreiscSeoRedirect';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $dreiscSeoRedirectId;

    /**
     * @var int|null
     */
    protected $rowIndex;

    /**
     * @var array|null
     */
    protected $rowValue;

    /**
     * @var array|null
     */
    protected $errors;

    /**
     * @var DreiscSeoRedirectEntity|null
     */
    protected $dreiscSeoRedirect;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDreiscSeoRedirectId(): ?string
    {
        return $this->dreiscSeoRedirectId;
    }

    public function setDreiscSeoRedirectId(?string $dreiscSeoRedirectId): self
    {
        $this->dreiscSeoRedirectId = $dreiscSeoRedirectId;

        return $this;
    }

    public function getRowIndex(): ?int
    {
        return $this->rowIndex;
    }

    public function setRowIndex(?int $rowIndex): self
    {
        $this->rowIndex = $rowIndex;

        return $this;
    }

    public function getRowValue(): ?array
    {
        return $this->rowValue;
    }

    public function setRowValue(?array $rowValue): self
    {
        $this->rowValue = $rowValue;

        return $this;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function setErrors(?array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function getDreiscSeoRedirect(): ?DreiscSeoRedirectEntity
    {
        return $this->dreiscSeoRedirect;
    }

    public function setDreiscSeoRedirect(?DreiscSeoRedirectEntity $dreiscSeoRedirect): self
    {
        $this->dreiscSeoRedirect = $dreiscSeoRedirect;

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
