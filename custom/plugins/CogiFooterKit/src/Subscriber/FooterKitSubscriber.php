<?php declare(strict_types=1);

namespace Cogi\CogiFooterKit\Subscriber;

use Cogi\CogiFooterKit\Core\Content\FooterKitEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;


class FooterKitSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityRepository
     */
    private $footerKitRepository;

    /**
     * @var EntityRepository
     */
    private $mediaRepository;

    /**
     * @var AbstractProductDetailRoute
     */
    private $productDetailRoute;

    /** 
     * @var EntityRepository
     */
    private $productRepository;
    

    public function __construct(
        EntityRepository $footerKitRepository,
        EntityRepository $mediaRepository,
        AbstractProductDetailRoute $productDetailRoute,
        EntityRepository $productRepository)
    {
        $this->footerKitRepository = $footerKitRepository;
        $this->mediaRepository = $mediaRepository;
        $this->productDetailRoute = $productDetailRoute;
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return[
            FooterPageletLoadedEvent::class => 'onHeaderLoaded'
        ];
    }

    public function onHeaderLoaded (FooterPageletLoadedEvent $event): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $event->getSalesChannelContext()->getSalesChannelId()));

        /** @var FooterKitEntity $footerKit */
        $footerKit = $this->footerKitRepository->search($criteria, $event->getContext())->getElements();

        if(empty($footerKit)){
            return;
        }

        $mediaCriteria = new Criteria();

        /** @var MediaEntity $media */
        $media = $this->mediaRepository->search($mediaCriteria, $event->getContext())->getElements();

//        dd(reset($footerKit));

        $productIds = reset($footerKit);


        $newProductIds = $productIds->informationConfig['dynamicProductSettings']['productIds'];
        $informationType = $productIds->informationConfig['basicSettings']['informationType'];
        $productType = $productIds->informationConfig['basicSettings']['productType'];
        $salesChannel = $productIds->informationConfig['basicSettings']['salesChannelNew'];

        if($productType == 'new'){
            $productCriteria = new Criteria();
            $productCriteria->addAssociation('visibilities');
            $productCriteria->addSorting(new FieldSorting("createdAt", FieldSorting::DESCENDING));
            $productCriteria->setLimit(intval($productIds->informationConfig['basicSettings']['numberOfNewProduct']));
            $productCriteria->addFilter(new MultiFilter( MultiFilter::CONNECTION_AND,
                                                        [
                                                            new EqualsFilter("active", true),
                                                            new EqualsFilter("visibilities.salesChannelId", $salesChannel)
                                                        ]
                                                    )
                                                );


            /** @var ProductEntity $productEn */
            $productEn = $this->productRepository->search($productCriteria, $event->getContext())->getIds();

            $newProduct = [];
            foreach($productEn as $id){
                $newProduct[] = $this->productDetailRoute->load($id , new Request(), $event->getSalesChannelContext(), new Criteria())->getProduct();
            }
        }


        if ($informationType == 'dynamic'){
            $product = [];
            foreach($newProductIds as $id){
                $product[] = $this->productDetailRoute->load($id , new Request(), $event->getSalesChannelContext(), new Criteria())->getProduct();
            }
        }
       
        $page =$event->getPagelet();
        $page->addExtension('CogiFooterKit', new ArrayEntity([
            'footerKit' => $footerKit
        ]));
        $page->addExtension('Media', new ArrayEntity([
            'media' => $media
        ]));
        if ($informationType == 'dynamic' and $productType == 'select'){
            $page->addExtension('Product', new ArrayEntity(([
                'product' => $product
            ])));
        }
        if ($informationType == 'dynamic' and $productType == 'new'){
            $page->addExtension('Product', new ArrayEntity(([
                'product' =>  $newProduct
            ])));
        }
    }

}
