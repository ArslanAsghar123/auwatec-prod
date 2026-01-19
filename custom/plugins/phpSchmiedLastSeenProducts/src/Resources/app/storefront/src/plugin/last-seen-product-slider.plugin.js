import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import HttpClient from 'src/service/http-client.service';
import ViewportDetection from 'src/helper/viewport-detection.helper';
import SliderSettingsHelper from 'src/plugin/slider/helper/slider-settings.helper';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

export default class LastSeenProductSliderPlugin extends Plugin {
    static options = {
        initializedCls: 'js-slider-initialized',
        containerSelector: '[data-base-slider-container=true]',
        controlsSelector: '[data-base-slider-controls=true]',
        url: '',
        productboxMinWidth: '300px',
        gutter: 10,
        slider: {
            enabled: true,
            responsive: {
                xs: {},
                sm: {},
                md: {},
                lg: {},
                xl: {},
                /** @deprecated tag:v6.5.0 - Bootstrap v5 adds xxl breakpoint */
                ...(Feature.isActive('V6_5_0_0') && {xxl: {}}),
            },
        },
    };

    init() {
        this._client = new HttpClient();
        this._fetchLastSeenProductUrl = window.lastSeenProductsFetchRoute;
        this._currentProductId = window.lastSeenProductId;

        this.fetch();
    }

    fetch() {
        const that = this;
        const containerSlider = DomAccess.querySelector(this.el, this.options.containerSelector);

        ElementLoadingIndicatorUtil.create(that.el);

        this._client.get(this._fetchLastSeenProductUrl + '?cms&productId=' + this._currentProductId, (response) => {
            ElementLoadingIndicatorUtil.remove(that.el);

            if (response === '') {
                this.el.remove();
                return;
            }

            containerSlider.innerHTML = response;

            this._sliderInstance = window.PluginManager.getPluginInstanceFromElement(this.el, 'BaseSlider');

            this._rebuildSlider();

            this._registerEvents();

            this.el.classList.remove('is-loading');

            this.$emitter.publish('fetch', {response});
        });
    }

    _registerEvents() {
        document.addEventListener('Viewport/hasChanged', () => {
            this._rebuildSlider()
        });
    }

    _rebuildSlider() {
        let viewport = ViewportDetection.getCurrentViewport();
        this._sliderInstance._sliderSettings = SliderSettingsHelper.getViewportSettings(this.options.slider, viewport.toLowerCase());
        this._sliderInstance._sliderSettings.items = this._getSlideItems();
        this._sliderInstance.destroy();
        this._sliderInstance._initSlider();
    }

    _getSlideItems() {
        const containerWidth = this._getInnerWidth();

        const gutter = this.options.gutter;
        const itemWidth = parseInt(this.options.productboxMinWidth.replace('px', ''), 0);
        const itemLimit = Math.floor(containerWidth / (itemWidth + gutter));

        return Math.max(1, itemLimit);
    }

    _getInnerWidth() {
        const computedStyle = getComputedStyle(this.el);

        if (!computedStyle) return;

        // width with padding
        let width = this.el.clientWidth;

        width -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);

        return width;
    }
}