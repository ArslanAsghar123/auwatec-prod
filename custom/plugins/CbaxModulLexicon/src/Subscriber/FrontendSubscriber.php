<?php declare(strict_types=1);

namespace Cbax\ModulLexicon\Subscriber;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Shopware\Core\Defaults;

use Shopware\Core\Framework\Uuid\Uuid;

use Shopware\Storefront\Framework\Routing\RequestTransformer;

use Shopware\Storefront\Page\Product\QuickView\MinimalQuickViewPageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Suggest\SuggestPageLoadedEvent;

use Shopware\Core\System\SystemConfig\SystemConfigService;

use Cbax\ModulLexicon\Bootstrap\Database;

use Cbax\ModulLexicon\Components\LexiconReplacer;
use Cbax\ModulLexicon\Components\LexiconHelper;

class FrontendSubscriber implements EventSubscriberInterface
{
    private ?array $config = null;

    public function __construct(
        private readonly LexiconReplacer $lexiconReplacer,
        private readonly SystemConfigService $systemConfigService,
        private readonly LexiconHelper $helperComponent,
        private readonly Connection $connection
    ) {

    }

	public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
            MinimalQuickViewPageLoadedEvent::class => 'onQuickViewPageLoaded',
            SuggestPageLoadedEvent::class => 'onSuggestPageLoaded',
        ];
    }

    public function onSuggestPageLoaded(SuggestPageLoadedEvent $event): void
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();

        $this->config = $this->config ?? $this->systemConfigService->get(Database::CONFIG_PATH, $salesChannelId);
        if (empty($this->config['searchActive'])) {
            return;
        }
        $page = $event->getPage();

        $searchTerm = $page->getSearchTerm();
        $searchLimit = $this->config['searchLimit'] ?? 5;
        $minSearchLength = $this->getMinimalSearchLenght($event) ?? 3;
        if (is_string($searchTerm) && strlen($searchTerm) >= $minSearchLength) {

            $entries = $this->helperComponent->searchSuggestLexicon($searchTerm, (int)$searchLimit, $event->getContext());

            if ($entries->getTotal() > 0) {
                $page->assign( ['cbaxLexiconSuggestions' => $entries->getElements()]);
                $page->assign( ['cbaxLexiconSuggestionsTotal' => $entries->getTotal()]);
            }
        }
    }

    private function getMinimalSearchLenght(SuggestPageLoadedEvent $event): int
    {
        $languageId = $event->getSalesChannelContext()->getLanguageId();
        $languages = UUid::fromHexToBytesList([$languageId, Defaults::LANGUAGE_SYSTEM]);
        try {
            $sql = "SELECT LOWER(HEX(`language_id`)), `min_search_length` FROM `product_search_config` WHERE `language_id` IN (?)";
            $result = $this->connection->fetchAllKeyValue($sql, [$languages], [ArrayParameterType::STRING]);
        } catch (\Exception) {
            return 3;
        }

        return (int)($result[$languageId] ?? $result[Defaults::LANGUAGE_SYSTEM]);
    }

	public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $request = $event->getRequest();

        $salesChannelContext = $event->getSalesChannelContext();
        $salesChannelId = $salesChannelContext->getSalesChannelId();

        $this->config = $this->config ?? $this->systemConfigService->get(Database::CONFIG_PATH, $salesChannelId);

		if (!empty($this->config['active']))
		{
			if (!empty($this->config['activeArticle']) || !empty($this->config['activeProperties']))
			{
                $shopUrl = $request->attributes->get(RequestTransformer::STOREFRONT_URL);
                $context = $salesChannelContext->getContext();

				$page = $event->getPage();

                if (empty($page)) {
                    return;
                }

                $cmsPage = $page->getCmsPage();
                $product = $page->getProduct();

                if (empty($product)) {
                    return;
                }

                if (!empty($cmsPage)) {
                    $productDescriptionReviewElement = $cmsPage->getFirstElementOfType('product-description-reviews');
                    if (!empty($productDescriptionReviewElement) && !empty($productDescriptionReviewElement->getData()))
                    {
                        $cmsProduct = $productDescriptionReviewElement->getData()->getProduct();
                    }
                }

                // $this->config['activeProperties'] kann "name" und "value" als Array-Wert enthalten
				if (count($this->config['activeProperties']) > 0) {
					foreach ($product->getSortedProperties()->getElements() as $key => $propertyGroup) {
						if (in_array('name', $this->config['activeProperties'])) {
							$translatedGroup = $propertyGroup->getTranslated();
							$translatedGroup['name'] = $this->lexiconReplacer->getReplaceText($translatedGroup['name'], $salesChannelId, $shopUrl, $context, $this->config);

							$propertyGroup->setTranslated($translatedGroup);
                            if (!empty($cmsProduct)) {
                                $cmsProduct->getSortedProperties()->get($key)?->setTranslated($translatedGroup);
                            }
						}

                        if (in_array('value', $this->config['activeProperties'])) {
                            foreach ($propertyGroup->getOptions()?->getElements() as $optionId => $option) {
                                $translatedOption = $option->getTranslated();
                                $translatedOption['name'] = $this->lexiconReplacer->getReplaceText($translatedOption['name'], $salesChannelId, $shopUrl, $context, $this->config);

                                $propertyGroup->getOptions()->getElements()[$optionId]->setTranslated($translatedOption);
                                if (!empty($cmsProduct)) {
                                    $cmsProduct->getSortedProperties()->get($key)?->getOptions()?->get($optionId)?->setTranslated($translatedOption);
                                }
                            }
                        }
					}
				}

				if (!empty($this->config['activeArticle']) || !empty($this->config['activeProductCustomFields'])) {
					$productTranslated = $product->getTranslated();

                    if (!empty($this->config['activeArticle'])) {
                        $productTranslated['description'] = $this->lexiconReplacer->getReplaceText($productTranslated['description'], $salesChannelId, $shopUrl, $context, $this->config);
                    }

                    if (!empty($this->config['activeProductCustomFields']) && !empty($productTranslated['customFields'])) {
                        foreach ($this->config['activeProductCustomFields'] as $field) {
                            if (!empty($productTranslated['customFields'][$field])) {
                                $productTranslated['customFields'][$field] = $this->lexiconReplacer->getReplaceText($productTranslated['customFields'][$field], $salesChannelId, $shopUrl, $context, $this->config);
                            }
                        }
                    }

					$product->assign(['translated' => $productTranslated]);

                    if (!empty($cmsProduct)) {
                        $cmsProduct->assign(['translated' => $productTranslated]);
                    }
				}
			}
		}
    }

    public function onQuickViewPageLoaded (MinimalQuickViewPageLoadedEvent $event): void
	{
        $request = $event->getRequest();
        $salesChannelContext = $event->getSalesChannelContext();
        $salesChannelId = $salesChannelContext->getSalesChannelId();
        $this->config = $this->config ?? $this->systemConfigService->get(Database::CONFIG_PATH, $salesChannelId);

		if (!empty($this->config['active']) && !empty($this->config['activeArticle']))
		{
            $shopUrl = $request->attributes->get(RequestTransformer::STOREFRONT_URL);

            $page = $event->getPage();
            if (empty($page)) {
                return;
            }

            $product = $page->getProduct();
            if (empty($product)) {
                return;
            }

            $productTranslated = $product->getTranslated();
            $productTranslated['description'] = $this->lexiconReplacer->getReplaceText($productTranslated['description'], $salesChannelId, $shopUrl, $salesChannelContext->getContext(), $this->config);

            $product->assign(['translated' => $productTranslated]);
		}
    }
}
