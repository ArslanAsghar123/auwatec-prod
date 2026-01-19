<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\DAL\Backup;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

/**
 * Backup entity
 *
 * @package   Swpa\SwpaBackup\DAL\Backup
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class SwpaBackupEntity extends Entity
{
    
    use EntityIdTrait;
    
    /**
     * @var string
     */
    protected $comment;
    
    /**
     * @var int
     */
    protected $status;
    
    /**
     * @var string
     */
    protected $filename;
    
    /**
     * @var int
     */
    protected $time;
    
    /**
     * @var string
     */
    protected $filesystem;
    
    /**
     * @var bool
     */
    protected $deleted;
    
    const STATUS_DELETED_SUCCESS = 1, STATUS_DELETED_NON = 0, STATUS_DELETED_ERROR = 2;
    
    /**
     * @return bool
     */
    public function getDeleted(): bool
    {
        return $this->deleted;
    }
    
    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }
    
    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
    
    /**
     * @return int|null
     */
    public function getTime(): ?int
    {
        
        return $this->time;
    }
    
    /**
     * @param int $time
     */
    public function setTime(int $time): void
    {
        
        $this->time = $time;
    }
    
    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }
    
    /**
     * @return string
     */
    public function getFilesystem(): string
    {
        return $this->filesystem;
    }
    
    /**
     * @param string $filesystem
     */
    public function setFilesystem(string $filesystem): void
    {
        $this->filesystem = $filesystem;
    }
    
    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
    
    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
    
    
    /**
     * @return string
     */
    public function getComment(): string
    {
        
        return $this->comment;
    }
    
    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
    
}
