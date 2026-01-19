//import ChooseBusinessAccountPlugin from "./js/plugins/choose-business-account.plugin";

//window.PluginManager.deregister('CollapseFooterColumns', '[data-collapse-footer]');

window.PluginManager.register('Bracket', () => import('./js/plugins/bracket.plugin'), '.bracket');
window.PluginManager.register('Seotext', () => import('./js/plugins/seotext.plugin'), '[data-seo-text]');
window.PluginManager.register('Accordion', () => import('./js/plugins/accordion.plugin'), '[data-accordion]');
window.PluginManager.override('FlyoutMenu', () => import('./js/plugins/flyout-menu-override.plugin'), '[data-flyout-menu]');
window.PluginManager.register('AccountMenu', () => import('./js/plugins/account-menu-override.plugin'), '[data-offcanvas-account-menu]');

// PluginManager.register('ChooseBusinessAccount', ChooseBusinessAccountPlugin);

function initLazyBackgrounds() {
    const lazyBackgrounds = document.querySelectorAll('[data-bg-url]');

    if ('IntersectionObserver' in window) {
        let lazyBackgroundObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let el = entry.target;
                    el.style.backgroundImage = 'url(' + el.dataset.bgUrl + ')';
                    el.removeAttribute('data-bg-url');
                    lazyBackgroundObserver.unobserve(el);
                }
            });
        });

        lazyBackgrounds.forEach(function(el) {
            lazyBackgroundObserver.observe(el);
        });
    } else {
        // Fallback für ältere Browser ohne Intersection Observer Unterstützung
        lazyBackgrounds.forEach(function(el) {
            el.style.backgroundImage = 'url(' + el.dataset.bgUrl + ')';
        });
    }
}

document.onreadystatechange = function () {
    if (document.readyState === 'complete') {
        initLazyBackgrounds();
    }
};