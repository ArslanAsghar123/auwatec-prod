import OffCanvasMenu from 'src/plugin/main-menu/offcanvas-menu.plugin';
import CookieConfiguration from 'src/plugin/cookie/cookie-configuration.plugin';

export default class LoyOffCanvasMenuPlugin extends OffCanvasMenu {


    init() {

        super.init();

        const cookieConfiguration = window.PluginManager.getPluginInstances("CookieConfiguration");
        this.cookieConfig = null;
        if(cookieConfiguration.length > 0) {
            this.cookieConfig = cookieConfiguration[0];
        }
    }

    /**
     * returns the handler for the passed navigation link
     *
     * @param {Event} event
     * @param {Element} link
     * @private
     */
    _getLinkEventHandler(event, link) {

        if (link && link.classList.contains('loyxx-sidebar-configuration-menu')) {
            this._openCookieConfiguration(event);
        }else{
            super._getLinkEventHandler(event, link);
        }

    }

    /**
     * Open the cookie configuration window
     *
     * @param {Event} event
     * @private
     */
    _openCookieConfiguration(event) {
        let cookieConfiguration = new CookieConfiguration(this.cookieConfig.el, this.cookieConfig.options, this.cookieConfig._pluginName);
        cookieConfiguration.openOffCanvas();
        event.preventDefault();
        event.stopImmediatePropagation();
    }
}
