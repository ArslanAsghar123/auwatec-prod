<?php declare(strict_types=1);

namespace DreiscSeoPro\Administration\Controller;

use DreiscSeoPro\Core\BulkGenerator\AiTemplateGenerator;
use DreiscSeoPro\Core\BulkGenerator\Message\BulkGeneratorMessage;
use DreiscSeoPro\Core\BulkGenerator\Message\BulkGeneratorMessageService;
use DreiscSeoPro\Core\Content\Language\LanguageRepository;
use DreiscSeoPro\Core\BulkGenerator\CategoryTemplateGenerator;
use DreiscSeoPro\Core\BulkGenerator\ProductTemplateGenerator;
use DreiscSeoPro\Core\BulkGenerator\TemplateGenerator\Struct\TemplateGeneratorStruct;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\Aggregate\DreiscSeoBulkTemplate\DreiscSeoBulkTemplateRepository;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkRepository;
use RuntimeException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;

#[Route(defaults: ['_routeScope' => ['api']])]
class DreiscSeoBulkController extends AbstractController
{
    /**
     * @var DreiscSeoBulkTemplateRepository
     */
    private $dreiscSeoBulkTemplateRepository;

    /**
     * @param DreiscSeoBulkTemplateRepository $dreiscSeoBulkTemplateRepository
     */
    public function __construct(
        private readonly DreiscSeoBulkRepository $dreiscSeoBulkRepository,
        DreiscSeoBulkTemplateRepository $dreiscSeoBulkTemplateRepository,
        private readonly CategoryTemplateGenerator $categoryBulkGenerator,
        private readonly ProductTemplateGenerator $productBulkGenerator,
        private readonly LanguageRepository $languageRepository,
        private readonly BulkGeneratorMessageService $bulkGeneratorMessageService,
        private readonly AiTemplateGenerator $aiTemplateGenerator
    )
    {
        $this->dreiscSeoBulkTemplateRepository = $dreiscSeoBulkTemplateRepository;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    #[Route(path: '/api/dreisc.seo/dreisc.seo.bulk/getResponsibleSeoBulk', defaults: ['auth_required' => true])]
    public function getResponsibleSeoBulk(Request $request, RequestDataBag $requestDataBag): JsonResponse
    {
        $bulkTemplateInformation = [];

        /** @var array{categoryIds: array, area: string, seoOption: string, languageId: string, salesChannelId: ?string} $requestParams */
        $requestParams = $requestDataBag->all();

        /** Check the required parameters */
        if (empty($requestParams['categoryIds'])) {
            throw new RuntimeException('Missing parameter: categoryIds');
        }
        $categoryIds = $requestParams['categoryIds'];

        if (empty($requestParams['area'])) {
            throw new RuntimeException('Missing parameter: area');
        }
        $area = $requestParams['area'];

        if (empty($requestParams['seoOption'])) {
            throw new RuntimeException('Missing parameter: seoOption');
        }
        $seoOption = $requestParams['seoOption'];

        $languageId = $request->request->get('languageId');
        if (empty($languageId)) {
            throw new RuntimeException('Missing parameter: languageId');
        }

        if (!empty($requestParams['salesChannelId'])) {
            $salesChannelId = $requestParams['salesChannelId'];
        } else {
            $salesChannelId = null;
        }

        foreach($categoryIds as $categoryId) {
            /** Load the responsible seo bulk configuration */
            $dreiscSeoBulkEntity = $this->dreiscSeoBulkRepository->getResponsibleSeoBulk(
                $categoryId,
                $area,
                $seoOption,
                $languageId,
                $salesChannelId
            );

            $bulkTemplateInformation[] = [
                'categoryId' => $categoryId,
                'seoBulkEntity' => $dreiscSeoBulkEntity
            ];
        }

        return new JsonResponse($bulkTemplateInformation);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    #[Route(path: '/api/dreisc.seo/dreisc.seo.bulk/getResponsibleProductSeoBulkRespectPriority', defaults: ['auth_required' => true])]
    public function getResponsibleProductSeoBulkRespectPriority(Request $request): JsonResponse
    {
        /** Check the required parameters */
        $productId = $request->request->get('productId');
        if (empty($productId)) {
            throw new RuntimeException('Missing parameter: productId');
        }

        $seoOption = $request->request->get('seoOption');
        if (empty($seoOption)) {
            throw new RuntimeException('Missing parameter: seoOption');
        }

        $languageId = $request->request->get('languageId');
        if (empty($languageId)) {
            throw new RuntimeException('Missing parameter: languageId');
        }

        $salesChannelId = $request->request->get('salesChannelId');
        if (empty($salesChannelId)) {
            $salesChannelId = null;
        }

        /** Load the responsible seo bulk configuration */
        $dreiscSeoBulkEntity = $this->dreiscSeoBulkRepository->getResponsibleProductSeoBulkRespectPriority(
            $productId,
            $seoOption,
            $languageId,
            $salesChannelId
        );

        return new JsonResponse($dreiscSeoBulkEntity);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    #[Route(path: '/api/dreisc.seo/dreisc.seo.bulk/deleteBulkTemplate', defaults: ['auth_required' => true])]
    public function deleteBulkTemplate(Request $request): JsonResponse
    {
        $bulkTemplateInformation = [];
        $limit = 5;

        /** Check the required parameters */
        $seoBulkTemplateId = $request->request->get('seoBulkTemplateId');
        if (empty($seoBulkTemplateId)) {
            throw new RuntimeException('Missing parameter: seoBulkTemplateId');
        }

        $deleteSeoBulkWhichUseTemplate = $request->request->get('deleteSeoBulkWhichUseTemplate');

        /** Fetch the details of the current bulk template */
        $seoBulkTemplateEntity = $this->dreiscSeoBulkTemplateRepository->get($seoBulkTemplateId);

        /** Fetch seo bulks which use the template */
        $seoBulkSearchResult = $this->dreiscSeoBulkRepository->getSeoBulkListBySeoBulkTemplateId($seoBulkTemplateId, $limit);
        if ($seoBulkSearchResult->getTotal() > 0) {
            if (!empty($deleteSeoBulkWhichUseTemplate)) {
                $this->dreiscSeoBulkRepository->deleteBySeoBulkTemplateId($seoBulkTemplateId);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'seoBulkTemplateEntity' => $seoBulkTemplateEntity,
                    'seoBulkEntriesWithCurrentTemplate' => $seoBulkSearchResult->getEntities(),
                    'seoBulkEntriesWithCurrentTemplateTotal' => $seoBulkSearchResult->getTotal()
                ]);
            }
        }

        /** Delete the bulk template */
        $this->dreiscSeoBulkTemplateRepository->delete([
            [ 'id' => $seoBulkTemplateId ]
        ]);

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    #[Route(path: '/api/dreisc.seo/dreisc.seo.bulk/getTemplatePreview', defaults: ['auth_required' => true])]
    public function getTemplatePreview(Request $request): JsonResponse
    {
        $area = $request->request->get('area');
        $activeItemId = $request->request->get('activeItemId');
        $seoOption = $request->request->get('seoOption');
        $languageId = $request->request->get('languageId');
        $salesChannelId = $request->request->get('salesChannelId');
        $template = $request->request->get('template');
        $spaceless = (bool) $request->request->get('spaceless');
        $aiPrompt = (bool) $request->request->get('aiPrompt');

        /** Abort if the template is null or empty */
        if(empty($template)) {
            return new JsonResponse([
                'success' => true,
                'renderedTemplate' => ''
            ]);
        }

        try {
            if (DreiscSeoBulkEnum::AREA__CATEGORY === $area) {
                $renderedTemplate = $this->categoryBulkGenerator->generateTemplate(
                    new TemplateGeneratorStruct(
                        $area,
                        $activeItemId,
                        $seoOption,
                        $languageId,
                        $salesChannelId,
                        $spaceless,
                        null,
                        $aiPrompt
                    ),
                    $template
                );
            } elseif (DreiscSeoBulkEnum::AREA__PRODUCT === $area) {
                $renderedTemplate = $this->productBulkGenerator->generateTemplate(
                    new TemplateGeneratorStruct(
                        $area,
                        $activeItemId,
                        $seoOption,
                        $languageId,
                        $salesChannelId,
                        $spaceless,
                        null,
                        $aiPrompt
                    ),
                    $template
                );
            } else {
                throw new RuntimeException(sprintf(
                    'Invalid area "%s"',
                    $area
                ));
            }

            return new JsonResponse([
                'success' => true,
                'renderedTemplate' => $aiPrompt ? nl2br($renderedTemplate) : $renderedTemplate
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    #[Route(path: '/api/dreisc.seo/dreisc.seo.bulk/getAiTemplatePreview', defaults: ['auth_required' => true])]
    public function getAiTemplatePreview(Request $request): JsonResponse
    {
        $area = $request->request->get('area');
        $seoOption = $request->request->get('seoOption');
        $prompt = $request->request->get('prompt');

        /** Abort if the template is null or empty */
        if(empty($prompt)) {
            return new JsonResponse([
                'success' => true,
                'renderedTemplate' => ''
            ]);
        }

        try {
            return new JsonResponse([
                'success' => true,
                'renderedTemplate' => $this->aiTemplateGenerator->generate($prompt, $seoOption)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    #[Route(path: '/api/dreisc.seo/dreisc.seo.bulk/getCurrenBulkGeneratorState', defaults: ['auth_required' => true])]
    public function getCurrenBulkGeneratorState(Request $request): JsonResponse
    {
        $area = $request->request->get('area');
        if (empty($area)) {
            throw new RuntimeException('Missing parameter: area');
        }

        $currentState = $this->bulkGeneratorMessageService->getCurrentState(
            $area
        );

        return new JsonResponse(
            array_merge([
                'success' => true
            ], $currentState)
        );
    }

    #[Route(path: '/api/dreisc.seo/dreisc.seo.bulk/runBulkGeneratorThread', defaults: ['auth_required' => true])]
    public function runBulkGeneratorThread(Request $request): JsonResponse
    {
        $area = $request->request->get('area');

        if (empty($area)) {
            throw new RuntimeException('Missing parameter: area');
        }

        $this->bulkGeneratorMessageService->dispatchBulkGenerator($area);

        return new JsonResponse([
            'success' => true
        ]);
    }
}
