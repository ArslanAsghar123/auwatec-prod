<?php declare(strict_types=1);

namespace Intedia\Doofinder\Core\Content\Update\Service;

use Intedia\Doofinder\Core\Content\Settings\Service\CommunicationHandler;
use Intedia\Doofinder\Core\Content\Settings\Service\SettingsHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ExportHandler
 * @package Intedia\Doofinder\Core\Content\ProductExport\Service
 */
class UpdateHandler
{
    /** @var Context $context */
    protected Context $context;

    /** @var EntityRepository $productExportRepository */
    protected EntityRepository $productExportRepository;

    /** @var SettingsHandler $settingsHandler */
    protected SettingsHandler $settingsHandler;

    /** @var SystemConfigService $systemConfigService */
    protected SystemConfigService $systemConfigService;

    /** @var TranslatorInterface $translator */
    protected TranslatorInterface $translator;

    /** @var CommunicationHandler|null $communicationHandler */
    protected ?CommunicationHandler $communicationHandler;

    const CONFIG_KEY = 'IntediaDoofinderSW6.config.';

    public function __construct(
        EntityRepository      $productExportRepository,
        SettingsHandler       $settingsHandler,
        SystemConfigService   $systemConfigService,
        TranslatorInterface   $translator,
        ?CommunicationHandler $communicationHandler = null
    )
    {
        $this->productExportRepository = $productExportRepository;
        $this->settingsHandler         = $settingsHandler;
        $this->systemConfigService     = $systemConfigService;
        $this->translator              = $translator;
        $this->communicationHandler    = $communicationHandler;
    }

    public function updateDoofinderTo200(): bool
    {
        /** @var SalesChannelEntity $storeFrontChannel */
        foreach ($this->settingsHandler->getStoreFrontChannels() as $storeFrontChannel) {

            $knownLanguages = [];
            /** @var SalesChannelDomainEntity $domain */
            foreach ($storeFrontChannel->getDomains() as $domain) {
                $languageCode = $domain->getLanguage()->getLocale()->getCode();
                if (!in_array($languageCode, $knownLanguages)) {

                    // is SSL domain
                    if (strpos($domain->getUrl(), 'https://') === 0) {
                        $translationHash = $this->translator->trans("intedia-doofinder.configuration.hashId") !== "intedia-doofinder.configuration.hashId" ? $this->translator->trans("intedia-doofinder.configuration.hashId") : null;
                        $pluginConfig    = $this->systemConfigService->getDomain(self::CONFIG_KEY, $this->settingsHandler->getContext() ? $storeFrontChannel->getId(): null, true);

                        if (!empty($pluginConfig[self::CONFIG_KEY . 'apiKey']) && (!empty($pluginConfig[self::CONFIG_KEY . 'engineHashId']) || !empty($translationHash)) && !empty($pluginConfig[self::CONFIG_KEY . 'searchDomain'])) {

                            $storeId = $pluginConfig[self::CONFIG_KEY . 'installationId'];
                            $hashId = $translationHash ? $translationHash : $pluginConfig[self::CONFIG_KEY . 'engineHashId'];

                            $this->communicationHandler->createDoofinderExportForDomainIfRequired(
                                $storeFrontChannel,
                                $domain,
                                $storeId,
                                $hashId
                            );
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Updates the feed to be more robust against missing data
     */
    public function updateDoofinderExport101(): void
    {
        foreach ($this->settingsHandler->getDooFinderChannels() as $doofinderChannel) {

            $updateData = [];

            foreach ($doofinderChannel->getProductExports() as $productExport) {

                $template = $productExport->getBodyTemplate();

                $template = str_replace("<g:item_group_id>{{ product.parentId }}</g:item_group_id>", "<g:item_group_id>{% if product.parentId %}{{ product.parentId }}{% else %}{{ product.id }}{% endif %}</g:item_group_id>", $template);
                $template = str_replace("<g:product_type>{{ product.categories.first.getBreadCrumb|slice(1)|join(' > ')|raw|escape }}</g:product_type>", "{% if product.categories and product.categories.first and product.categories.first.getBreadCrumb %}<g:product_type>{{ product.categories.first.getBreadCrumb|slice(1)|join(' > ')|raw|escape }}</g:product_type> {% endif %}", $template);
                $template = str_replace("<g:image_link>{{ product.cover.media.url }}</g:image_link>", "{% if product.cover and product.cover.media %}<g:image_link>{{ product.cover.media.url }}</g:image_link> {% endif %}", $template);
                $template = str_replace("<g:brand>{{ product.manufacturer.translated.name|escape }}</g:brand>", "{% if product.manufacturer %}<g:brand>{{ product.manufacturer.translated.name|escape }}</g:brand> {% endif %}", $template);

                $updateData[] = [
                    'id' => $productExport->getId(),
                    'bodyTemplate' => $template,
                ];
            }

            if (!empty($updateData)) {
                $this->productExportRepository->update($updateData, $this->settingsHandler->getContext());
            }
        }
    }

    public function updateDoofinderExport105(): void
    {
        foreach ($this->settingsHandler->getDooFinderChannels() as $doofinderChannel) {

            $updateData = [];

            foreach ($doofinderChannel->getProductExports() as $productExport) {

                $template = $productExport->getBodyTemplate();

                $template = str_replace("    <g:price>{{ product.calculatedListingPrice.from.unitPrice|number_format(context.currency.decimalPrecision, '.', '') }} {{ context.currency.isoCode }}</g:price>", "    {% set price = product.calculatedPrice %}
    {% if product.calculatedPrices.count > 0 %}
    {% set price = product.calculatedPrices.last %}
    {% endif %}

    <g:price>{{ price.unitPrice|number_format(context.currency.itemRounding.decimals, '.', '') }} {{ context.currency.isoCode }}</g:price>", $template);

                $updateData[] = [
                    'id' => $productExport->getId(),
                    'bodyTemplate' => $template,
                ];
            }

            if (!empty($updateData)) {
                $this->productExportRepository->update($updateData, $this->settingsHandler->getContext());
            }
        }
    }

    public function updateDoofinderExport108(): void
    {
        foreach ($this->settingsHandler->getDooFinderChannels() as $doofinderChannel) {

            $updateData = [];

            foreach ($doofinderChannel->getProductExports() as $productExport) {

                $template = $productExport->getBodyTemplate();

                $template  = str_replace("{% if product.cover and product.cover.media %}<g:image_link>{{ product.cover.media.url }}</g:image_link> {% endif %}", "{% if product.cover and product.cover.media %}<g:image_link>{{ product.cover.media.url }}</g:image_link>{% elseif product.media and product.media.first and product.media.media %}<g:image_link>{{ product.media.first.media.url }}</g:image_link>{% endif %}", $template);

                if (strpos($template, "<search_keywords>") === false) {
                    $template  = str_replace("</item>", "    <search_keywords>{% if product.customSearchKeywords|length %}{{ product.customSearchKeywords|map( term => term|replace({'/': '\/'}))|join('/') }}{% endif %}</search_keywords>
</item>", $template);
                }

                $updateData[] = [
                    'id' => $productExport->getId(),
                    'bodyTemplate' => $template,
                ];
            }

            if (!empty($updateData)) {
                $this->productExportRepository->update($updateData, $this->settingsHandler->getContext());
            }
        }
    }


    public function updateDoofinderExport109(): void
    {
        foreach ($this->settingsHandler->getDooFinderChannels() as $doofinderChannel) {

            $updateData = [];

            foreach ($doofinderChannel->getProductExports() as $productExport) {

                $template = $productExport->getBodyTemplate();

                $template = str_replace("{% if product.categories and product.categories.first and product.categories.first.getBreadCrumb %}<g:product_type>{{ product.categories.first.getBreadCrumb|slice(1)|join(' > ')|raw|escape }}</g:product_type> {% endif %}", "<g:product_type>{{ doofinderCategoryPath(product.categories, productExport.salesChannel.navigationCategoryId, context)|join(' %% ') }}</g:product_type>", $template);
                $template = str_replace("
    <g:google_product_category>950{# change your Google Shopping category #}</g:google_product_category>", "", $template);

                $updateData[] = [
                    'id' => $productExport->getId(),
                    'bodyTemplate' => $template,
                ];
            }

            if (!empty($updateData)) {
                $this->productExportRepository->update($updateData, $this->settingsHandler->getContext());
            }
        }
    }

    public function updateDoofinderExport110(): void
    {
        foreach ($this->settingsHandler->getDooFinderChannels() as $doofinderChannel) {

            $updateData = [];

            foreach ($doofinderChannel->getProductExports() as $productExport) {

                $template = $productExport->getBodyTemplate();

                $template = str_replace("<g:item_group_id>{% if product.parentId %}{{ product.parentId }}{% else %}{{ product.id }}{% endif %}</g:item_group_id>", "<g:item_group_id>{{ doofinderGroupId(product, context.context) }}</g:item_group_id>", $template);
                $template = str_replace("    {% set price = product.calculatedPrice %}
    {% if product.calculatedPrices.count > 0 %}
    {% set price = product.calculatedPrices.last %}
    {% endif %}

    <g:price>{{ price.unitPrice|number_format(context.currency.itemRounding.decimals, '.', '') }} {{ context.currency.isoCode }}</g:price>", "    {% set price = product.calculatedPrice %}
    {% if product.calculatedPrices.count > 0 %}
        {% set price = product.calculatedPrices.last %}
    {% endif %}
    {% set listPrice = price.listPrice %}

    {% if listPrice %}
        <g:sale_price>{{ price.unitPrice|number_format(context.currency.itemRounding.decimals, '.', '') }} {{ context.currency.isoCode }}</g:sale_price>
        <g:price>{{ listPrice.price|number_format(context.currency.itemRounding.decimals, '.', '') }} {{ context.currency.isoCode }}</g:price>
    {% else %}
        <g:price>{{ price.unitPrice|number_format(context.currency.itemRounding.decimals, '.', '') }} {{ context.currency.isoCode }}</g:price>
    {% endif %}", $template);
                $template = str_replace("    {% if product.cover and product.cover.media %}<g:image_link>{{ product.cover.media.url }}</g:image_link>{% elseif product.media and product.media.first and product.media.media %}<g:image_link>{{ product.media.first.media.url }}</g:image_link>{% endif %}", "    {% if product.cover and product.cover.media %}
        {% if product.cover.media.thumbnails|length > 0 %}
            {% for thumbnail in product.cover.media.thumbnails %}
                {% if thumbnail.width == '400' %}
                    <g:image_link>{{ thumbnail.url }}</g:image_link>
                {% endif %}
            {% endfor %}
        {% else %}
            <g:image_link>{{ product.cover.media.url }}</g:image_link>
        {% endif %}
    {% elseif product.media and product.media.first and product.media.media %}
        {% if product.media.first.media.thumbnails|length > 0 %}
            {% for thumbnail in product.media.first.media.thumbnails %}
                {% if thumbnail.width == '400' %}
                    <g:image_link>{{ thumbnail.url }}</g:image_link>
                {% endif %}
            {% endfor %}
        {% else %}
            <g:image_link>{{ product.media.first.media.url }}</g:image_link>
        {% endif %}
    {% endif %}", $template);
                $template = str_replace("        {%- if product.availableStock >= product.minPurchase and product.deliveryTime -%}
        in_stock
        {%- elseif product.availableStock < product.minPurchase and product.deliveryTime and product.restockTime -%}
        preorder
        {%- else -%}
        out_of_stock
        {%- endif -%}", "        {%- if product.availableStock >= product.minPurchase or product.isCloseout == false -%}
        in_stock
        {%- else -%}
        out_of_stock
        {%- endif -%}", $template);

                $updateData[] = [
                    'id' => $productExport->getId(),
                    'bodyTemplate' => $template,
                ];
            }

            if (!empty($updateData)) {
                $this->productExportRepository->update($updateData, $this->settingsHandler->getContext());
            }
        }
    }

    public function updateDoofinderExport221(): void
    {
        foreach ($this->settingsHandler->getDooFinderChannels() as $doofinderChannel) {

            $updateData = [];

            foreach ($doofinderChannel->getProductExports() as $productExport) {

                $template = $productExport->getBodyTemplate();

                $template = str_replace(
                    "<g:product_type>{{ doofinderCategoryPath(product.categories, productExport.salesChannel.navigationCategoryId, context)|join(' %% ') }}</g:product_type>",
                    "<g:product_type>{{ doofinderCategories(product, productStreamCategories, categoryTree)|join(' %% ') }}</g:product_type>",
                    $template);

                $template = str_replace(
                    "<g:item_group_id>{{ doofinderGroupId(product, context.context) }}</g:item_group_id>",
                    "<g:item_group_id>{{ doofinderGroupId(product, groupIds, context.context) }}</g:item_group_id>",
                    $template);

                $updateData[] = [
                    'id' => $productExport->getId(),
                    'bodyTemplate' => $template,
                ];
            }

            if (!empty($updateData)) {
                $this->productExportRepository->update($updateData, $this->settingsHandler->getContext());
            }
        }
    }

    public function updateDoofinderExport224(): void
    {
        foreach ($this->settingsHandler->getDooFinderChannels() as $doofinderChannel) {

            $updateData = [];

            foreach ($doofinderChannel->getProductExports() as $productExport) {

                $template = $productExport->getBodyTemplate();

                $template = str_replace(
                    "{{ product.translated.description",
                    "{{ imClean(product.translated.description)",
                    $template);

                $template = str_replace(
                    "<description>{{ product.translated.description|escape }}</description>",
                    "<description><![CDATA[ {{ imClean(product.translated.description)|escape }} ]]></description>",
                    $template);

                $updateData[] = [
                    'id' => $productExport->getId(),
                    'bodyTemplate' => $template,
                ];
            }

            if (!empty($updateData)) {
                $this->productExportRepository->update($updateData, $this->settingsHandler->getContext());
            }
        }
    }

    public function deleteDooFinderStream(): bool
    {
        return $this->settingsHandler->deleteDooFinderStream();
    }

    public function deleteDooFinderExports(): bool
    {
        return $this->settingsHandler->deleteDooFinderExports();
    }
}