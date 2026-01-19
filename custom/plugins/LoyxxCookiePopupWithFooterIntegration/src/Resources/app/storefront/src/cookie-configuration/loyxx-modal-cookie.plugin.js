import DeviceDetection from 'src/helper/device-detection.helper';
import CookieStorage from 'src/helper/storage/cookie-storage.helper';
import AjaxOffCanvas from 'src/plugin/offcanvas/ajax-offcanvas.plugin';

export default class LoyxxModalCookiePlugin extends window.PluginBaseClass {

    static options = {
        buttonSelector: '.open-cookie-settings',
        denyButtonSelector: '.js-cookie-permission-button',
        saveButtonSelector: '.js-offcanvas-cookie-submit',
        cookieList: '.offcanvas-cookie-list',
        cookieSelector: '[data-cookie]',
        /**
         * cookie expiration time
         * the amount of days until the cookie bar will be displayed again
         */
        cookieExpiration: 30,

        /**
         * cookie set to determine if cookies were accepted or denied
         */
        cookieName: 'cookie-preference',
    };

    init() {

        this._button = this.el.querySelector(this.options.buttonSelector);
        this._denyButton = this.el.querySelector(this.options.denyButtonSelector);
        this._saveButton = this.el.querySelector(this.options.saveButtonSelector);
        this._registerEventListeners();
    }

    _registerEventListeners() {
        if (this._button) {
            const submitEvent = (DeviceDetection.isTouchDevice()) ? 'touchstart' : 'click';
            this._button.addEventListener(submitEvent, this._onClickOpenCookieButton.bind(this));
        }

        if (this._denyButton) {
            const submitDenyEvent = (DeviceDetection.isTouchDevice()) ? 'touchstart' : 'click';
            this._denyButton.addEventListener(submitDenyEvent, this._onClickDenyCookieButton.bind(this));
        }
    }

    _onClickOpenCookieButton() {
        this._list = this.el.querySelector(this.options.cookieList);
        if (this._list) {
            this._list.style.display = 'block';
            this._button.style.display = 'none';
            this._denyButton.style.display = 'none';
            this._saveButton.style.display = 'block';
        }
    }

    _onClickDenyCookieButton(event) {
        event.preventDefault();

        const {cookieExpiration, cookieName} = this.options;
        const cookies = this._getCookies('all');
        cookies.forEach(({cookie, value, required, expiration}) => {
            const isActive = CookieStorage.getItem(cookie);
            if ('true' === required || isActive) {
                CookieStorage.setItem(cookie, value, expiration);
            } else {
                CookieStorage.removeItem(cookie);
            }
        });
        CookieStorage.setItem(cookieName, '1', cookieExpiration);
        AjaxOffCanvas.close();

        this.$emitter.publish('onClickDenyButton');
    }

    /**
     * Get cookies passed to the configuration template
     * Can be filtered by "all", "active" or "inactive"
     *
     * Always excludes "required" cookies, since they are assumed to be set separately.
     *
     * @param type
     * @param offCanvas
     * @returns {Array}
     * @private
     */
    _getCookies(type = 'all', offCanvas = null) {
        const {cookieSelector} = this.options;

        return Array.from(this.el.querySelectorAll(cookieSelector)).filter(cookieInput => {
            switch (type) {
                case 'all':
                    return true;
                case 'active':
                    return this._isChecked(cookieInput);
                case 'inactive':
                    return !this._isChecked(cookieInput);
                default:
                    return false;
            }
        }).map(filteredInput => {
            const {cookie, cookieValue, cookieExpiration, cookieRequired} = filteredInput.dataset;
            return {cookie, value: cookieValue, expiration: cookieExpiration, required: cookieRequired};
        });
    }

    _isChecked(target) {
        return !!target.checked;
    }

    _onClickSideBarClickMenu() {
        console.log('clicked');
    }
}
