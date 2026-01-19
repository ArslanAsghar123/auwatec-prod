const PluginManager = window.PluginManager;

PluginManager.register('CbaxModulLexiconSearchSuggest', () => import('./script/cbax-modul-lexicon-search-suggest.plugin'), '[data-search-widget]');

if (!window.Feature.isActive('ACCESSIBILITY_TWEAKS')) {
    const pluginNames = Object.keys(PluginManager.getPluginList());
    let doRegister = true;

    if (pluginNames) {
        for (const pn of pluginNames) {
            if (pn.startsWith('CbaxFocusSlider')) {
                doRegister = false;
                break;
            }
        }
    }

    if (doRegister) {
        PluginManager.register('CbaxFocusSliderLexicon', () => import('./script/cbax-focus-slider-lexicon.plugin'), '[data-product-slider]');
    }
}

// Necessary for the webpack hot module reloading server
if (module.hot) {
    module.hot.accept();
}
