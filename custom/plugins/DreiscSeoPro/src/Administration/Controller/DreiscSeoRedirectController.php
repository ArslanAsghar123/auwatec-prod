<?php declare(strict_types=1);

namespace DreiscSeoPro\Administration\Controller;

use League\Flysystem\FilesystemOperator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportFile\DreiscSeoRedirectImportExportFileService;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\Aggregate\DreiscSeoRedirectImportExportLog\DreiscSeoRedirectImportExportLogRepository;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectDefinition;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use League\Flysystem\FileNotFoundException;
use RuntimeException;
use Shopware\Core\Content\ImportExport\Aggregate\ImportExportFile\ImportExportFileEntity;
use Shopware\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use Shopware\Core\Content\ImportExport\Exception\FileNotReadableException;
use Shopware\Core\Content\ImportExport\Exception\ProcessingException;
use Shopware\Core\Content\ImportExport\Exception\UnexpectedFileTypeException;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

#[Route(defaults: ['_routeScope' => ['api']])]
class DreiscSeoRedirectController extends AbstractController
{
    /**
     * @var FilesystemOperator
     */
    private $filesystem;

    /**
     * @var EntityRepository
     */
    private $fileRepository;

    public function __construct(private readonly Connection $connection, private readonly EntityCacheKeyGenerator $entityCacheKeyGenerator, private readonly CacheClearer $cacheClearer, private readonly DataValidator $dataValidator, private readonly DreiscSeoRedirectImportExportFileService $dreiscSeoRedirectImportExportFileService, FilesystemOperator $filesystem, EntityRepository $fileRepository, private readonly DreiscSeoRedirectImportExportLogRepository $dreiscSeoRedirectImportExportLogRepository, private readonly DreiscSeoRedirectRepository $dreiscSeoRedirectRepository)
    {
        $this->filesystem = $filesystem;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @throws DBALException
     */
    #[Route(path: '/api/dreisc.seo.pro/dreisc.seo.redirect/deleteSeoRedirect', defaults: ['auth_required' => true])]
    public function deleteSeoRedirect(Request $request): JsonResponse
    {

        $dreiscSeoRedirectId = $request->request->get('dreiscSeoRedirectId');
        if(empty($dreiscSeoRedirectId)) {
            throw new RuntimeException('Missing param "dreiscSeoRedirectId"');
        }

        /**
         * This is a workaround because the entity is not deletable by the shopware DAL
         * @see: https://issues.shopware.com/issues/NEXT-7866
         */
        $this->dreiscSeoRedirectRepository->plainDeleteById($dreiscSeoRedirectId);

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @throws DBALException
     */
    #[Route(path: '/api/dreisc.seo.pro/dreisc.seo.redirect/prepareImport', defaults: ['auth_required' => true])]
    public function prepareImport(Request $request, Context $context): JsonResponse
    {
        $params = $request->request->all();
        $definition = new DataValidationDefinition();
        $definition->add('expireDate', new NotBlank(), new Type('string'));
        $this->dataValidator->validate($params, $definition);

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        $expireDate = new DateTimeImmutable($params['expireDate']);

        $dreiscSeoRedirectImportExportFileEntity = $this->dreiscSeoRedirectImportExportFileService->prepareImport(
            $context,
            $expireDate,
            $file,
            $params['config'] ?? []
        );

        unlink($file->getPathname());

        return new JsonResponse([
            'success' => true,
            'fileId' => $dreiscSeoRedirectImportExportFileEntity->getId()
        ]);
    }

    /**
     * @throws DBALException
     */
    #[Route(path: '/api/dreisc.seo.pro/dreisc.seo.redirect/prepareExport', defaults: ['auth_required' => true])]
    public function prepareExport(Request $request, Context $context): JsonResponse
    {
        $params = $request->request->all();
        $definition = new DataValidationDefinition();
        $definition->add('expireDate', new NotBlank(), new Type('string'));
        $this->dataValidator->validate($params, $definition);

        $expireDate = new DateTimeImmutable($params['expireDate']);

        $dreiscSeoRedirectImportExportFileEntity = $this->dreiscSeoRedirectImportExportFileService->prepareExport(
            $context,
            $expireDate
        );

        return new JsonResponse([
            'success' => true,
            'fileId' => $dreiscSeoRedirectImportExportFileEntity->getId(),
            'accessToken' => $dreiscSeoRedirectImportExportFileEntity->getAccessToken()
        ]);
    }

    /**
     * @throws DBALException
     */
    #[Route(path: '/api/dreisc.seo.pro/dreisc.seo.redirect/processImport', defaults: ['auth_required' => true])]
    public function processImport(Request $request, Context $context): JsonResponse
    {
        $params = $request->request->all();
        $definition = new DataValidationDefinition();
        $definition->add('dreiscSeoRedirectImportExportFileId', new NotBlank(), new Type('string'));
        $definition->add('offset', new NotBlank(), new Type('int'));
        $definition->add('delimiter', new NotBlank(), new Type('string'));
        $this->dataValidator->validate($params, $definition);

        $dreiscSeoRedirectImportExportFileId = strtolower((string) $params['dreiscSeoRedirectImportExportFileId']);
        $offset = $params['offset'];
        $delimiter = $params['delimiter'];

        $progress = $this->dreiscSeoRedirectImportExportFileService->processImport(
            $context,
            $offset,
            $delimiter,
            $dreiscSeoRedirectImportExportFileId,
            50
        );

        return new JsonResponse(['progress' => $progress->jsonSerialize()]);
    }

    /**
     * @throws DBALException
     */
    #[Route(path: '/api/dreisc.seo.pro/dreisc.seo.redirect/processExport', defaults: ['auth_required' => true])]
    public function processExport(Request $request, Context $context): JsonResponse
    {
        $params = $request->request->all();
        $definition = new DataValidationDefinition();
        $definition->add('dreiscSeoRedirectImportExportFileId', new NotBlank(), new Type('string'));
        $definition->add('offset', new NotBlank(), new Type('int'));
        $this->dataValidator->validate($params, $definition);

        $dreiscSeoRedirectImportExportFileId = strtolower((string) $params['dreiscSeoRedirectImportExportFileId']);
        $offset = $params['offset'];

        $progress = $this->dreiscSeoRedirectImportExportFileService->processExport(
            $context,
            $offset,
            $dreiscSeoRedirectImportExportFileId,
            50
        );

        return new JsonResponse(['progress' => $progress->jsonSerialize()]);
    }

    /**
     * @throws DBALException
     */
    #[Route(path: '/api/dreisc.seo.pro/dreisc.seo.redirect/fetchImportResult', defaults: ['auth_required' => true])]
    public function fetchImportResult(Request $request, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->dreiscSeoRedirectImportExportLogRepository->getResult()
        );
    }

    /**
     * @Route("/api/dreisc.seo.pro/dreisc.seo.redirect/download", name="api.action.import_export.file.download", defaults={"auth_required"=false}, methods={"GET"})
     */
    /**
     * @throws DBALException
     */
    #[Route(path: '/api/dreisc.seo.pro/dreisc.seo.redirect/download', defaults: ['auth_required' => false], methods: ['GET'])]
    public function download(Request $request, Context $context): Response
    {
        $params = $request->query->all();
        $definition = new DataValidationDefinition();
        $definition->add('fileId', new NotBlank(), new Type('string'));
        $definition->add('accessToken', new NotBlank(), new Type('string'));
        $this->dataValidator->validate($params, $definition);

        $response = $this->dreiscSeoRedirectImportExportFileService->createFileResponse($context, $params['fileId'], $params['accessToken']);

        return $response;
    }
}
