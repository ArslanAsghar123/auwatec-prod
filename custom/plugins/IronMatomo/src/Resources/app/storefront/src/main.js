// Import all necessary Storefront plugins and scss files
import IronMatomo from './js/iron-matomo.plugin';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('IronMatomo', IronMatomo);

// Necessary for the webpack hot module reloading server
if (module.hot) {
    module.hot.accept();
}
