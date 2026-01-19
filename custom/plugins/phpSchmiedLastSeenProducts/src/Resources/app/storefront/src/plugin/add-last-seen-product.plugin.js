import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class AddLastSeenProductPlugin extends Plugin {
    static options = {
    }

    init() {
        this._client = new HttpClient();
        this._addLastSeenProductUrl = window.lastSeenProductAddRoute;

        this._registerEvents();
    }

    _registerEvents() {
        if (!window.lastSeenProductId) {
            console.error('window.lastSeenProductId is missing..');
            return;
        }

        const productId = window.lastSeenProductId;
        const payload = JSON.stringify({ productId });
        this._client.post(this._addLastSeenProductUrl, payload);
    }
}