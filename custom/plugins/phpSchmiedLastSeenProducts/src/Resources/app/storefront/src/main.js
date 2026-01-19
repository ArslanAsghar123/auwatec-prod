import LastSeenProductSliderPlugin from "./plugin/last-seen-product-slider.plugin";
import AddLastSeenProductPlugin from "./plugin/add-last-seen-product.plugin";
import LastSeenProductsPlugin from './plugin/last-seen-products.plugin.js';
import LastSeenProductsTabPlugin from './plugin/last-seen-products-tab.plugin.js';

const PluginManager = window.PluginManager;

//Normale Seite
PluginManager.register('LastSeenProducts', LastSeenProductsPlugin, '[data-last-seen-products]');
PluginManager.register('LastSeenProductsTab', LastSeenProductsTabPlugin, '[data-last-seen-products-tab]');
PluginManager.register('LastSeenProductsTabHeader', LastSeenProductsTabPlugin, '[data-last-seen-products-header]');
//CMS
PluginManager.register('AddLastSeenProduct', AddLastSeenProductPlugin, '.is-ctl-product.is-act-index');
PluginManager.register('LastSeenProductSlider', LastSeenProductSliderPlugin, '[data-last-seen-product-slider]');