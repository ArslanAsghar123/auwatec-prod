<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Content\Product\SalesChannel\Suggest;

use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\SalesChannel\Suggest\AbstractProductSuggestRoute;
use Shopware\Core\Content\Product\SalesChannel\Suggest\CachedProductSuggestRoute;
use Shopware\Core\Content\Product\SalesChannel\Suggest\ProductSuggestRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\Entity;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class UncachedProductSuggestRoute extends AbstractProductSuggestRoute
{
    private AbstractProductSuggestRoute $decorated;

    public function __construct(AbstractProductSuggestRoute $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getDecorated(): AbstractProductSuggestRoute
    {
        return $this->decorated;
    }

    /**
     * @Since("6.2.0.0")
     * @Entity("product")
     * @OA\Post(
     *      path="/search-suggest",
     *      summary="Search for products (suggest)",
     *      description="Can be used to implement search previews or suggestion listings, that don’t require any interaction.",
     *      operationId="searchSuggest",
     *      tags={"Store API","Product"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                  "search"
     *              },
     *              @OA\Property(
     *                  property="search",
     *                  description="Using the search parameter, the server performs a text search on all records based on their data model and weighting as defined in the entity definition using the SearchRanking flag.",
     *                  type="string"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Returns a product listing containing all products and additional fields.

    Note: Aggregations, currentFilters and availableSortings are empty in this response. If you need them to display a listing, use the /search route instead.",
     *          @OA\JsonContent(ref="#/components/schemas/ProductListingResult")
     *     )
     * )
     * @Route("/store-api/search-suggest", name="store-api.search.suggest", methods={"POST"})
     */
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ProductSuggestRouteResponse
    {
        // avoid caching of product suggest route, since that conflicts with doofinder relevance sorting
        if ($this->decorated instanceof CachedProductSuggestRoute) {
            return $this->decorated->getDecorated()->load($request, $context, $criteria);
        }

        return $this->decorated->load($request, $context, $criteria);
    }
}
