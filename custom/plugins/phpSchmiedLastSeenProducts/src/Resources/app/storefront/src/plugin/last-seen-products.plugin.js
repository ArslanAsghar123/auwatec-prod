import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

export default class LastSeenProductsPlugin extends Plugin {
    init() {
        this._client = new HttpClient();
        this._fetchLastSeenProductUrl = window.lastSeenProductsFetchRoute;
        this._currentProductId = window.lastSeenProductId;

        this._onlyCms = this.el.getAttribute('data-only-cms');

        if(this._onlyCms === '0') {
            this.fetch();
        }
    }

    fetch() {
        ElementLoadingIndicatorUtil.create(this.el);
        this._client.get(this._fetchLastSeenProductUrl + '?productId=' + this._currentProductId, (response) => {
            this.handleData(response);
        });
    }

    handleData(response) {
        this.el.innerHTML = response;
        ElementLoadingIndicatorUtil.remove(this.el);
        PluginManager.initializePlugin('ProductSlider', '.product-slider');
    }
}
