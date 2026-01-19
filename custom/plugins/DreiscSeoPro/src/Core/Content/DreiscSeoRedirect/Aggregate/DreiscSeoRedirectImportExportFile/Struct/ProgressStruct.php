<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Struct;

use DreiscSeoPro\Core\Foundation\Struct\DefaultStruct;

class ProgressStruct extends DefaultStruct
{
    final public const STATE_PROGRESS = 'progress';
    final public const STATE_MERGING_FILES = 'merging_files';
    final public const STATE_SUCCEEDED = 'succeeded';
    final public const STATE_FAILED = 'failed';
    final public const STATE_ABORTED = 'aborted';

    /**
     * @var string
     */
    protected $fileId;

    /**
     * @var int
     */
    protected $processedRecords = 0;

    /**
     * @var string
     */
    protected $state;

    /**
     * @param int $offset
     */
    public function __construct(string $FileId, string $state, protected $offset = 0, protected ?int $total = null)
    {
        $this->fileId = $FileId;
        $this->state = $state;
    }

    public function addProcessedRecords(int $processedRecords): void
    {
        $this->processedRecords += $processedRecords;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getProcessedRecords(): ?int
    {
        return $this->processedRecords;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function isFinished(): bool
    {
        return $this->getState() === self::STATE_SUCCEEDED
            || $this->getState() === self::STATE_FAILED
            || $this->getState() === self::STATE_ABORTED;
    }
}
