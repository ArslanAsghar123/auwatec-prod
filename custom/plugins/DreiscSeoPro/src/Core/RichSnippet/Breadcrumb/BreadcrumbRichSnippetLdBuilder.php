<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Breadcrumb;

use DreiscSeoPro\Core\Content\Category\CategoryRepository;
use DreiscSeoPro\Core\Content\Category\CategorySearchResult;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Breadcrumb\HomeStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Breadcrumb\ProductStruct;
use DreiscSeoPro\Core\Foundation\Seo\SeoUrlAssembler;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\Tree\TreeItem;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Adapter\Translation\AbstractTranslator;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Framework\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class BreadcrumbRichSnippetLdBuilder implements BreadcrumbRichSnippetLdBuilderInterface
{
    public function __construct(private readonly SeoUrlAssembler $seoUrlAssembler, private readonly CategoryRepository $categoryRepository, private readonly AbstractTranslator $translator, private readonly RouterInterface $router)
    {
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function build(BreadcrumbRichSnippetLdBuilderStruct $breadcrumbRichSnippetLdBuilderStruct): ?array
    {
        $plainBreadcrumb = $breadcrumbRichSnippetLdBuilderStruct->getPlainBreadcrumb();
        $breadcrumbSettings = $breadcrumbRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getBreadcrumb();

        /** Abort, if there is no breadcrumb category*/
        if(0 === count($plainBreadcrumb)) {
            return null;
        }

        /** Abort, if the breadcrumb should not be displayed */
        if (false === $breadcrumbSettings->getGeneral()->isActive()) {
            return null;
        }

        /** Create the base information */
        $ld = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList'
        ];

        /** Iterate the navigation */
        /** @var TreeItem $treeItem */
        $listElements = [];

        /** Create an entry for the start page, if configured */
        if (in_array($breadcrumbSettings->getHome()->getShowInBreadcrumbMode(), [
            HomeStruct::SHOW_IN_BREADCRUMB_MODE__ONLY_JSON_LD,
            HomeStruct::SHOW_IN_BREADCRUMB_MODE__SHOP_AND_JSON_LD
        ], true)) {
            /** Build the list element */
            $listElements[] = $this->buildHomeListElement(
                $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelEntity(),
                $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelDomainId()
            );
        }

        foreach($plainBreadcrumb as $categoryId => $categoryName) {

            /** Build the list element */
            $listElements[] = $this->buildCategoryListElement(
                $categoryId,
                $categoryName,
                $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelContext(),
                $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelEntity(),
                $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelDomainId()
            );
        }

        /** Create an entry for the product if necessary */
        $productEntity = $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelProductEntity();
        if (null !== $productEntity) {
            if (in_array($breadcrumbSettings->getProduct()->getShowInBreadcrumbMode(), [
                ProductStruct::SHOW_IN_BREADCRUMB_MODE__ONLY_JSON_LD,
                ProductStruct::SHOW_IN_BREADCRUMB_MODE__SHOP_AND_JSON_LD
            ], true)) {
                /** Build the list element */
                $listElements[] = $this->buildProductListElement(
                    $productEntity,
                    $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelContext(),
                    $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelEntity(),
                    $breadcrumbRichSnippetLdBuilderStruct->getSalesChannelDomainId()
                );
            }
        }

        /** Filter empty entries */
        $listElements = array_filter($listElements);

        /** Abort, if empty */
        if(empty($listElements)) {
            return null;
        }

        /** Update the positions */
        $position = 0;
        foreach($listElements as &$listElement) {
            $listElement['position'] = ++$position;
        }

        /** Add the itemList elements */
        $ld['itemListElement'] = $listElements;

        return $ld;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function buildCategoryListElement(string $categoryId, string $categoryName, SalesChannelContext $salesChannelContext, SalesChannelEntity $salesChannelEntity, string $salesChannelDomainId): array
    {
        $breadcrumb = [
            '@type' => 'ListItem',
            'position' => 0,
            'name' => $categoryName
        ];

        /** Fake a category entity for the assembler */
        $categoryEntity = new CategoryEntity();
        $categoryEntity->setId($categoryId);
        $categoryEntity->setName($categoryName);

        /** Try to fetch the url of the category */
        $categoryUrl = $this->seoUrlAssembler->assemble(
            $categoryEntity,
            $salesChannelEntity->getId(),
            $salesChannelContext->getLanguageId()
        );

        if(
            null !== $salesChannelDomainId &&
            !empty($categoryUrl[SeoUrlAssembler::ABSOLUTE_PATHS]) &&
            !empty($categoryUrl[SeoUrlAssembler::ABSOLUTE_PATHS][$salesChannelDomainId])
        ) {
            $breadcrumb['item'] = $categoryUrl[SeoUrlAssembler::ABSOLUTE_PATHS][$salesChannelDomainId];
        } elseif (
            '/detail/' === substr($categoryUrl['technicalPathInfo'], 0, 8) ||
            '/navigation/' === substr($categoryUrl['technicalPathInfo'], 0, 12)
        ) {
            $breadcrumb['item'] = $categoryUrl['technicalPathInfo'];
        }

        return $breadcrumb;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function buildProductListElement(SalesChannelProductEntity $salesChannelProductEntity, SalesChannelContext $salesChannelContext, SalesChannelEntity $salesChannelEntity, string $salesChannelDomainId): array
    {
        /** Translated product */
        $translatedProduct = $salesChannelProductEntity->getTranslated();

        /** Breadcrumb name */
        $breadcrumbName = !empty($translatedProduct['name']) ? $translatedProduct['name'] : $salesChannelProductEntity->getName();

        $breadcrumb = [
            '@type' => 'ListItem',
            'position' => 0,
            'name' => $breadcrumbName
        ];

        /** Try to fetch the url of the category */
        $categoryUrl = $this->seoUrlAssembler->assemble(
            $salesChannelProductEntity,
            $salesChannelEntity->getId(),
            $salesChannelContext->getLanguageId()
        );

        if(
            null !== $salesChannelDomainId &&
            !empty($categoryUrl[SeoUrlAssembler::ABSOLUTE_PATHS]) &&
            !empty($categoryUrl[SeoUrlAssembler::ABSOLUTE_PATHS][$salesChannelDomainId])
        ) {
            $breadcrumb['item'] = $categoryUrl[SeoUrlAssembler::ABSOLUTE_PATHS][$salesChannelDomainId];
        } elseif ('/detail/' === substr($categoryUrl['technicalPathInfo'], 0, 8)) {
            $breadcrumb['item'] = $categoryUrl['technicalPathInfo'];
        }

        return $breadcrumb;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function buildHomeListElement(SalesChannelEntity $salesChannelEntity, string $salesChannelDomainId): array
    {
        /** Breadcrumb name */
        $breadcrumbName = $this->translator->trans('dreiscSeoPro.richSnippets.breadcrumb.homeText');

        /** Create the home link */
        $homeLink = $this->router->generate('frontend.home.page', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $breadcrumb = [
            '@type' => 'ListItem',
            'position' => 0,
            'name' => $breadcrumbName,
            'item' => $homeLink
        ];

        return $breadcrumb;
    }

    /**
     * @return CategorySearchResult
     * @throws InconsistentCriteriaIdsException
     */
    private function loadCategories(BreadcrumbRichSnippetLdBuilderStruct $breadcrumbRichSnippetLdBuilderStruct): EntitySearchResult
    {
        $categoryIds = array_keys($breadcrumbRichSnippetLdBuilderStruct->getPlainBreadcrumb());

        return $this->categoryRepository->search(
            (new Criteria($categoryIds))
                ->addSorting(
                    new FieldSorting('level', FieldSorting::ASCENDING)
                )
        );
    }
}
