import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import DomAccess from 'src/helper/dom-access.helper';
import ViewportDetection from 'src/helper/viewport-detection.helper';

export default class LastSeenProductsTabPlugin extends Plugin {
    static options = {
        tabSelector: 'a[data-bs-toggle="tab"]',
        productSliderSelector: '[data-product-slider="true"]',
    };

    init() {
        this._client = new HttpClient();
        this._fetchLastSeenProductUrl = window.lastSeenProductsFetchRoute;
        this._currentProductId = window.lastSeenProductId;
        this._active = this.el.getAttribute(' data-tab-active');

        if(this.el.classList.contains('last-seen-header')) {
            this.fetchHeader();
        }

        if(this.el.classList.contains('last-seen-tab')) {
            this.fetch();
        }
    }

    fetchHeader() {
        this._client.get(this._fetchLastSeenProductUrl + '?tab=1&active=' + (this._active !== null ? 1 : 0) + '&header=1&productId=' + this._currentProductId, (response) => {
            this.el.innerHTML = response;

            if(response !== '') {
                this._registerEvents();
            }
        });
    }

    fetch() {
        ElementLoadingIndicatorUtil.create(this.el);
        this._client.get(this._fetchLastSeenProductUrl + '?tab=1&productId=' + this._currentProductId, (response) => {
            this.handleData(response);
        });
    }

    handleData(response) {
        this.el.innerHTML = response;
        ElementLoadingIndicatorUtil.remove(this.el);
        PluginManager.initializePlugin('ProductSlider', '.product-slider');
    }

    _registerEvents() {
        const crossSellingTabs = DomAccess.querySelectorAll(this.el, this.options.tabSelector);
        crossSellingTabs.forEach((tab) => {
            tab.addEventListener('shown.bs.tab', this._rebuildCrossSellingSlider.bind(this));
        });
    }

    _rebuildCrossSellingSlider(event) {
        if (!event.target.hasAttribute('id')) {
            return;
        }

        const id = event.target.id;
        const correspondingContent = DomAccess.querySelector(document, `#${id}-pane`);

        const slider = DomAccess.querySelector(correspondingContent, this.options.productSliderSelector, false);

        if (slider === false) {
            return;
        }

        const sliderInstance = window.PluginManager.getPluginInstanceFromElement(slider, 'ProductSlider');

        sliderInstance.rebuild(ViewportDetection.getCurrentViewport(), true);
    }
}
