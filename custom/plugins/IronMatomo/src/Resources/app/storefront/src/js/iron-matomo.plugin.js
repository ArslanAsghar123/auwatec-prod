import Plugin from 'src/plugin-system/plugin.class';
import CookieStorage from 'src/helper/storage/cookie-storage.helper';
import { COOKIE_CONFIGURATION_UPDATE } from 'src/plugin/cookie/cookie-configuration.plugin';
// import StoreApiClient from 'src/service/store-api-client.service';

export default class IronMatomo extends Plugin {

    init() {
        // const client = new StoreApiClient;
        // client.get('/store-api/v2/category/58d87c563e174f7fac0c1d75d417a8fa', function(response) {
        //     const data = JSON.parse(response);
        //     console.log('Categorie', data._uniqueIdentifier, data.name);
        // });
        if (!window.ironMatomoDataLayer) {
            return;
        }
        this.isInitAndTracked = false;
        this._dataLayer = window.ironMatomoDataLayer || {};
        this.startMatomo();
        this._registerEvents();
    }

    _registerEvents() {
        // Fallback for webpack undefined exception
        const cookieConfigurationUpdate = COOKIE_CONFIGURATION_UPDATE ? COOKIE_CONFIGURATION_UPDATE : 'CookieConfiguration_Update';
        document.$emitter.subscribe(cookieConfigurationUpdate, (updatedCookies) => {
            if (typeof updatedCookies.detail.ironMatomo !== 'undefined'
                && updatedCookies.detail.ironMatomo) {
                this.startMatomo();
            }
        });
    }

    startMatomo() {
        if (this.ready()) {
            this._initAndTrack();
        }
    }

    ready() {
        return this._dataLayer && window._paq && (this._isTrackAllowed() || this._dataLayer.startTracking == '4');
    }

    _isTrackAllowed() {
        let trackAllowed = false;
        if (this._dataLayer.startTracking == '0') {
            trackAllowed = CookieStorage.getItem('ironMatomo') === 'active';
        }
        if (this._dataLayer.startTracking == '1') {
            trackAllowed = this._isEnabled();
        }
        // Cookies erlaubt und requireCockieConsent aktive
        if (trackAllowed && this._dataLayer.requireCookieConsent == '1') {
            window._paq.track.push(['setCookieConsentGiven']);
        }
        if (this._dataLayer.startTracking == '3') {
            trackAllowed = true;
        }
        return this._dataLayer.requireCookieConsent == '1' || trackAllowed;
    }

    _isEnabled() {
        if ('' === this._dataLayer.cookieName.trim()) {
            return false;
        }
        const cookieValue = CookieStorage.getItem(this._dataLayer.cookieName);
        if (this._dataLayer.cookieValueAsRegex == '1') {
            const regexPatt = this._dataLayer.cookieValue;
            const cleanRegEx = regexPatt.replace(/&quot;/gi, `"`);
            if (cookieValue.match(cleanRegEx)) {
                return true;
            }
        }
        return cookieValue === this._dataLayer.cookieValue;
    }

    _initAndTrack() {
        if (this.isInitAndTracked) {
            return;
        }
        this._loadMatomoScript();
        this.isInitAndTracked = true;
    }

    _loadMatomoScript() {
        var widget = this;
        if (this._dataLayer.startTracking == '4') {
            return;
        }
        (function () {
            var u = widget._dataLayer.matomoUrl;
            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
            g.type = 'text/javascript';
            g.async = true;
            g.defer = true;
            g.src = u + widget._dataLayer.matomoScript;
            s.parentNode.insertBefore(g, s);
        })()
    }
}
