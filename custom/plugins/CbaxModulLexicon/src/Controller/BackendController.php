<?php declare(strict_types=1);

namespace Cbax\ModulLexicon\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Attribute\Route;

use Shopware\Core\Framework\Adapter\Cache\CacheClearer;

use Shopware\Core\Framework\Context;

use Cbax\ModulLexicon\Components\LexiconMigration;
use Cbax\ModulLexicon\Components\LexiconSeo;
use Cbax\ModulLexicon\Components\LexiconSitemap;
use Cbax\ModulLexicon\Components\LexiconHelper;

#[Route(defaults: ['_routeScope' => ['administration']])]
class BackendController extends AbstractController
{
    public function __construct(
        private readonly LexiconSeo $lexiconSeo,
        private readonly LexiconSitemap $lexiconSitemap,
        private readonly LexiconHelper $lexiconHelper,
        protected readonly CacheClearer $cacheClearer,
        private readonly LexiconMigration $lexiconMigration
    ) {}

    #[Route(path: '/api/cbax/lexicon/seo', name: 'api.cbax.lexicon.seo', defaults: ['auth_required' => false], methods: ['GET'])]
    public function createSeoUrls(Request $request, Context $context): JsonResponse
    {
        $this->cacheClearer->clear();

        $adminLocalLanguage = trim($request->query->get('adminLocaleLanguage',''));

        $result = $this->lexiconSeo->createSeoUrls($context, $adminLocalLanguage);

        $this->lexiconSitemap->generateSitemap($context);

        return new JsonResponse($result);
    }

    #[Route(path: '/api/cbax/lexicon/seoDelete', name: 'api.cbax.lexicon.seoDelete', defaults: ['auth_required' => false], methods: ['GET'])]
    public function deleteSeoUrls(): JsonResponse
    {
        $result = $this->lexiconSeo->deleteSeoUrls();

        return new JsonResponse($result);
    }

    #[Route(path: '/api/cbax/lexicon/saveEntry', name: 'api.cbax.lexicon.saveEntry', defaults: ['auth_required' => true], methods: ['POST'])]
    public function saveEntry(Request $request, Context $context): JsonResponse
    {
        $entry = $request->request->all('entry');

        $languageId = $request->request->get('languageId','');
        $languageId = is_string($languageId) ? trim($languageId) : $languageId;

        $result = $this->lexiconHelper->saveEntry($entry, $languageId, $context);

        return new JsonResponse($result);
    }

    #[Route(path: '/api/cbax/lexicon/getProductCountList', name: 'api.cbax.lexicon.getProductCountList', defaults: ['auth_required' => true], methods: ['GET'])]
    public function getProductCountList(): JsonResponse
    {
        return new JsonResponse($this->lexiconHelper->getProductCountList());
    }

    #[Route(path: '/api/cbax/lexicon/getProductCountStream', name: 'api.cbax.lexicon.getProductCountStream', defaults: ['auth_required' => true], methods: ['POST'])]
    public function getProductCountStream(Request $request, Context $context): JsonResponse
    {
        $prodStreamEntries = $request->request->all()['prodStreamEntries'] ?? [];

        return new JsonResponse($this->lexiconHelper->getProductCountStream($prodStreamEntries, $context));
    }

    #[Route(path: '/api/cbax/lexicon/changeLexiconProducts', name: 'api.cbax.lexicon.changeLexiconProducts', defaults: ['auth_required' => true], methods: ['POST'])]
    public function changeLexiconProducts(Request $request): JsonResponse
    {
        $lexiconEntryId = $request->request->get('lexiconEntryId', '');
        $productId = $request->request->get('productId', '');
        $mode = $request->request->get('mode', '');

        return new JsonResponse($this->lexiconHelper->changeLexiconProducts($lexiconEntryId, $productId, $mode));
    }

    #[Route(path: '/api/cbax/lexicon/getLexiconProducts', name: 'api.cbax.lexicon.getLexiconProducts', defaults: ['auth_required' => true], methods: ['POST'])]
    public function getLexiconProducts(Request $request): JsonResponse
    {
        $productId = $request->request->get('productId', '');

        return new JsonResponse($this->lexiconHelper->getLexiconProductsEntries($productId));
    }

    #[Route(path: '/api/cbax/lexicon/importData', name: 'api.cbax.lexicon.importData', methods: ['POST'])]
    public function importData(Request $request, Context $context): JsonResponse
    {
        $start = $request->request->get('start', 0);

        $limit = $request->request->get('limit', 1000);

        $result = $this->lexiconMigration->importData($start, $limit, $this->lexiconHelper, $context);

        return new JsonResponse($result);
    }

    #[Route(path: '/api/cbax/lexicon/importProductAssignments', name: 'api.cbax.lexicon.importProductAssignments', methods: ['POST'])]
    public function importProductAssignments(Request $request, Context $context): JsonResponse
    {
        $start = $request->request->get('start');

        $limit = $request->request->get('limit', 3000);

        $result = $this->lexiconMigration->importProductAssignments($start, $limit, $this->lexiconHelper, $context);

        return new JsonResponse($result);
    }

    #[Route(path: '/api/cbax/lexicon/importShopAssignments', name: 'api.cbax.lexicon.importShopAssignments', methods: ['POST'])]
    public function importShopAssignments(Request $request, Context $context): JsonResponse
    {
        $start = $request->request->get('start');

        $limit = $request->request->get('limit', 3000);

        $result = $this->lexiconMigration->importShopAssignments($start, $limit, $this->lexiconHelper, $context);

        return new JsonResponse($result);
    }
}
