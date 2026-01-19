import CookieConfiguration from 'src/plugin/cookie/cookie-configuration.plugin';
import CookieStorage from 'src/helper/storage/cookie-storage.helper';

export default class LoyCookieConfiguration extends CookieConfiguration {
    init() {
        this.options.offCanvasPosition = 'modal-cookie';
        this.options.cookieName = 'cookie-preference';
        super.init();

        this.loadLoyCookieModal();
    }

    loadLoyCookieModal() {

        const cookiePermission = CookieStorage.getItem(this.options.cookieName);

        if (!cookiePermission) {
            this.openOffCanvas();
            return false;
        }

        return true;
    }
}
