<?php

namespace AkuCmsFactory\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Shopware\Core\Content\Cms\CmsPageEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Struct\StructCollection;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\Events\CmsPageLoadedEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use AkuCmsFactory\Services\CmsElementService;

use H1web\Blog\Storefront\Page\Blog\BlogPageLoadedEvent;

class CmsElementSubscriber implements EventSubscriberInterface {

    protected $cmsFactoryElementRepository;
    protected $CmsElementService;
    protected $SalesChannelContext;
    protected $current_route = '';

    public static function getSubscribedEvents(): array {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        $events = [
            // Der folgende Event enthält nicht die Übersetzungen
            // Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent
            //CmsPageEvents::PAGE_LOADED_EVENT => 'onCmsPageLoaded', 

            // Dieser Event enthält die Kategorie-Übersetzungen, wird getriggert in 
            // Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPage::load()
            // Shopware\Core\Content\Cms\Events\CmsPageLoadedEvent
            CmsPageLoadedEvent::class => 'onPageLoaded',

            BlogPageLoadedEvent::class => 'onBlogPageLoadedEvent',

            // Bei Produktseiten muss dieser Event ausgeführt werden
            CmsPageEvents::SLOT_LOADED_EVENT => 'onSlotLoaded',

            // Sales Channel Context merken für Event onSlotLoaded
            'kernel.controller_arguments' => 'onControllerArguments',

        ];
        return $events;
    }

    public function __construct(
        EntityRepository    $cmsFactoryElementRepository
        , CmsElementService $CmsElementService
    ) {
        $this->cmsFactoryElementRepository = $cmsFactoryElementRepository;
        $this->CmsElementService = $CmsElementService;
    }

    public function onControllerArguments($event) {
        $arguments = $event->getArguments();

        foreach ($arguments as $argument) {
            if (is_object($argument) && 'Shopware\Core\System\SalesChannel\SalesChannelContext' == get_class($argument)) {
                $this->SalesChannelContext = $argument;
            }
            if (is_object($argument) && 'Symfony\Component\HttpFoundation\Request' == get_class($argument)) {
                $this->current_route = $argument->get('_route');
            }
        }

    }

    /**
     * Event benötigt für die Anzeige von Erlebniswelten
     * z.B. mit Plugin sc_PlatformDetailEmotion
     */
    public function onSlotLoaded($event) {
        $entities = $event->getEntities();
        $context = $event->getContext();
        $sales_channel_context = $this->SalesChannelContext;
        foreach ($entities as $slot) {
            if ('aku-cms-factory' == $slot->getType()) {
                if (!str_contains($this->current_route, "api.cms_page.search")) {
                    if (in_array($this->current_route, ['frontend.navigation.page', 'frontend.home.page'])) {
                        // muss nicht ausgeführt werden. onPageLoaded-Event enthält die Übersetzungen und
                        // wird später ausgeführt
                        continue;
                    }
                    $this->updateAkuCmsSlot($slot, $context, $sales_channel_context);

                }
            }
        }
    }

    /**
     * Event benötigt für die Anzeige von Category-Seiten
     */
    public function onPageLoaded($event) {
        $entities = $event->getResult();
        $sales_channel_context = $event->getSalesChannelContext();
        $context = $event->getContext();
        foreach ($entities as $entity) {
            // Shopware\Core\Content\Cms\CmsPageEntity;
            $slots = $entity->getElementsOfType('aku-cms-factory');
            foreach ($slots as $slot) {
                $this->updateAkuCmsSlot($slot, $context, $sales_channel_context);
            }
        }
    }

    /**
     * Event benötigt für die Anzeige von Blog-Seiten mit dem H1-Blog-Plugin
     */
    public function onBlogPageLoadedEvent($event) {
        $entity = $event->getPage()->getCmsPage();
        $sales_channel_context = $event->getSalesChannelContext();
        $context = $event->getContext();
        // Shopware\Core\Content\Cms\CmsPageEntity;
        $slots = $entity->getElementsOfType('aku-cms-factory');
        foreach ($slots as $slot) {
            $this->updateAkuCmsSlot($slot, $context, $sales_channel_context);
        }
    }

    public function updateAkuCmsSlot(CmsSlotEntity $entity, $context, $sales_channel_context) {
        $slot_config = $entity->getConfig();
        $template = '';
        $fields = [];
        $id = isset($slot_config['cms_factory_element_id'])
            ? $slot_config['cms_factory_element_id']['value']
            : null;
        $field_values = [];
        //var_dump($slot_config['field_values']);die();
        if (isset($slot_config['field_values'])) {
            if (is_string($slot_config['field_values']['value'])) {
                // neuerdings als string gespeichert
                $field_values = json_decode($slot_config['field_values']['value'], true);
            } else {
                // Legacy
                $field_values = $slot_config['field_values']['value'];
            }
        }
        if ($id && Uuid::isValid($id)) {

            $criteria = new Criteria([$id]);
            $criteria->setLimit(1);
            $akuCmsFactory = $this->cmsFactoryElementRepository
                ->search($criteria, $context)->getEntities()->first();
            $template = null === $akuCmsFactory
                ? ''
                : $akuCmsFactory->getTwig();
            $fields = null === $akuCmsFactory || null === $akuCmsFactory->getFields()
                ? []
                : json_decode($akuCmsFactory->getFields(), true);
        }
        $data = $this->CmsElementService->getData($fields, $field_values, $context, $sales_channel_context);
        $data['__template'] = $template;
        $entity->setData(new ArrayStruct($data));
    }

}