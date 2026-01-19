<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile;

use League\Flysystem\FilesystemOperator;
use DateTimeInterface;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Exception\FileNotReadableException;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Exception\UnexpectedFileTypeException;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Import\RowExporter;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Import\RowImporter;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\Struct\ProgressStruct;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportLog\DreiscSeoRedirectImportExportLogRepository;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\Content\ImportExport\Processing\Reader\CsvReader;
use Shopware\Core\Content\ImportExport\Struct\Config;
use Shopware\Core\Content\ImportExport\Struct\Progress;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DreiscSeoRedirectImportExportFileService
{
    /**
     * @var DreiscSeoRedirectImportExportFileRepository
     */
    private $dreiscSeoRedirectImportExportFileRepository;

    /**
     * @var FilesystemOperator
     */
    private $filesystem;

    /**
     * @param DreiscSeoRedirectImportExportFileRepository $dreiscSeoRedirectImportExportFileRepository
     */
    public function __construct(DreiscSeoRedirectImportExportFileRepository $dreiscSeoRedirectImportExportFileRepository, FilesystemOperator $filesystem, private readonly RowImporter $rowImporter, private readonly DreiscSeoRedirectImportExportLogRepository $dreiscSeoRedirectImportExportLogRepository, private readonly RowExporter $rowExporter)
    {
        $this->dreiscSeoRedirectImportExportFileRepository = $dreiscSeoRedirectImportExportFileRepository;
        $this->filesystem = $filesystem;
    }

    public function prepareImport(Context $context, DateTimeInterface $expireDate, UploadedFile $file, array $config = []): DreiscSeoRedirectImportExportFileEntity
    {
        $type = $this->detectType($file);
        if ($type !== 'text/csv') {
            throw new UnexpectedFileTypeException($file->getClientMimeType());
        }

        $fileEntity = $this->storeFile(
            $context,
            $expireDate,
            $file->getPathname(),
            $file->getClientOriginalName(),
            'dreisc-seo-redirect-import',
            $config
        );

        /** Truncate the log */
        $this->dreiscSeoRedirectImportExportLogRepository->truncate();

        return $fileEntity;
    }

    public function prepareExport(Context $context, DateTimeInterface $expireDate): DreiscSeoRedirectImportExportFileEntity
    {
        $extension = 'csv';
        $timestamp = date('Ymd-His');
        $originalFileName = sprintf('%s_%s.%s', 'redirect-export', $timestamp, $extension);

        $fileEntity = $this->storeFile($context, $expireDate, null, $originalFileName, 'dreisc-seo-redirect-export', [], null);

        return $fileEntity;
    }

    public function processImport(Context $context, int $offset, string $delimiter, string $dreiscSeoRedirectImportExportFileId, int $batchSize = 50): Progress
    {
        $file = $this->getFile($dreiscSeoRedirectImportExportFileId);
        $progress = $this->getProgress($file, $offset);
        $progress->setTotal($file->getSize());

        if ($progress->isFinished()) {
            return $progress;
        }

        $processed = 0;

        $path = $file->getPath();
        $progress->setTotal($this->filesystem->fileSize($path));

        $csvReader = new CsvReader($delimiter);
        $resource = $this->filesystem->readStream($path);

        $config = new Config(
            [],
            [],
            []
        );

        foreach ($csvReader->read($config, $resource, $offset) as $row) {
            $this->rowImporter->import($row, $offset + $processed + 2);

            $progress->addProcessedRecords(1);
            $this->saveProgress($progress);

            if ($batchSize > 0 && ++$processed >= $batchSize) {
                break;
            }
        }

        $progress->setOffset($csvReader->getOffset());

        // importing the file is complete
        if ($csvReader->getOffset() === $this->filesystem->fileSize($path)) {
            $progress->setState(Progress::STATE_SUCCEEDED);
        }
        $this->saveProgress($progress);

        return $progress;
    }

    public function processExport(Context $context, int $offset, string $dreiscSeoRedirectImportExportFileId, int $batchSize = 50): Progress
    {
        $file = $this->getFile($dreiscSeoRedirectImportExportFileId);
        $progress = $this->getProgress($file, $offset);

        $tmpFile = tempnam(sys_get_temp_dir(), '');
        $tmp = fopen($tmpFile, 'w+b');

        $csvRows = $this->rowExporter->export(
            $progress,
            $offset,
            25
        );

        /** Create headlines */
        if (0 === $offset && !empty($csvRows)) {
            $headers = $csvRows[0]->toArray();
            unset($headers['extensions']);
            $headers = array_keys($headers);
            fputcsv($tmp, $headers, ';');
        }

        foreach($csvRows as $csvRow) {
            $csvRow = $csvRow->toArray();
            unset($csvRow['extensions']);
            fputcsv($tmp, $csvRow, ';');
        }

        $existingContent = $this->filesystem->read($file->getPath());
        $this->filesystem->write($file->getPath(), $existingContent . file_get_contents($tmpFile));

        if ($progress->isFinished()) {
            return $progress;
        }

        $this->saveProgress($progress);

        return $progress;
    }

    private function storeFile(Context $context, DateTimeInterface $expireDate, ?string $sourcePath, string $originalFileName, string $activity, array $config, ?string $path = null): DreiscSeoRedirectImportExportFileEntity
    {
        $id = Uuid::randomHex();
        $path ??= $activity . '/' . $this->buildPath($id);
        if (!empty($sourcePath)) {
            if (!is_readable($sourcePath)) {
                throw new FileNotReadableException($sourcePath);
            }
            $sourceStream = fopen($sourcePath, 'rb');
            if (!is_resource($sourceStream)) {
                throw new FileNotReadableException($sourcePath);
            }
            $this->filesystem->writeStream($path, $sourceStream);
        } else {
            $this->filesystem->write($path, '');
        }

        $fileData = [
            'id' => $id,
            'originalName' => $originalFileName,
            'path' => $path,
            'size' => $this->filesystem->fileSize($path),
            'expireDate' => $expireDate,
            'accessToken' => Random::getBase64UrlString(32),
            'activity' => $activity,
            'state' => ProgressStruct::STATE_PROGRESS,
            'records' => 0,
            'config' => $config
        ];

        $this->dreiscSeoRedirectImportExportFileRepository->create([$fileData], $context);

        $fileEntity = new DreiscSeoRedirectImportExportFileEntity();
        $fileEntity->assign($fileData);

        return $fileEntity;
    }

    private function detectType(UploadedFile $file): string
    {
        $guessedExtension = $file->guessClientExtension();
        if ($guessedExtension === 'csv' || $file->getClientOriginalExtension() === 'csv') {
            return 'text/csv';
        }

        return $file->getClientMimeType();
    }

    private function buildPath(string $id): string
    {
        return implode('/', str_split($id, 8));
    }

    private function getFile(string $fileId): DreiscSeoRedirectImportExportFileEntity
    {
        $current = $this->dreiscSeoRedirectImportExportFileRepository->search(new Criteria([$fileId]), Context::createDefaultContext())->first();
        if ($current === null) {
            throw new \RuntimeException('DreiscSeoRedirectImportExportFile "' . $fileId . '" not found');
        }

        return $current;
    }

    private function getProgress(DreiscSeoRedirectImportExportFileEntity $file, int $offset): Progress
    {
        $progress = new Progress(
            $file->getId(),
            $file->getState(),
            $offset
        );

        $progress->addProcessedRecords($file->getRecords());

        return $progress;
    }

    private function saveProgress(Progress $progress): void
    {
        $data = [
            'id' => $progress->getLogId(),
            'state' => $progress->getState(),
            'records' => $progress->getProcessedRecords(),
        ];

        $context = Context::createDefaultContext();
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($data): void {
            $this->dreiscSeoRedirectImportExportFileRepository->update([$data], $context);
        });
    }

    public function createFileResponse(Context $context, string $fileId, string $accessToken): Response
    {
        $entity = $this->findFile($context, $fileId);
        if ($entity->getAccessToken() !== $accessToken) {
            throw new \RuntimeException('Invalid access token');
        }

        $headers = [
            'Content-Disposition' => HeaderUtils::makeDisposition(
                'attachment',
                $entity->getOriginalName(),
                // only printable ascii
                preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $entity->getOriginalName())
            ),
            'Content-Length' => $this->filesystem->fileSize($entity->getPath()),
            'Content-Type' => 'application/octet-stream',
        ];
        $stream = $this->filesystem->readStream($entity->getPath());
        if (!is_resource($stream)) {
            throw new \RuntimeException('File not found');
        }

        return new StreamedResponse(function () use ($stream): void {
            fpassthru($stream);
        }, Response::HTTP_OK, $headers);
    }

    private function findFile(Context $context, string $fileId): DreiscSeoRedirectImportExportFileEntity
    {
        $entity = $this->dreiscSeoRedirectImportExportFileRepository->search(new Criteria([$fileId]), $context)->get($fileId);
        if ($entity === null) {
            throw new \RuntimeException('File not found');
        }

        return $entity;
    }
}
