import FlyoutMenuPlugin from "src/plugin/main-menu/flyout-menu.plugin";
import DeviceDetection from 'src/helper/device-detection.helper';
import DomAccess from 'src/helper/dom-access.helper';
import Iterator from 'src/helper/iterator.helper';

/**
 * Opens menu on click instead of mouseenter/-leave
 */
export default class FlyoutMenuOverride extends FlyoutMenuPlugin {
    _registerEvents() {
        const clickEvent = (DeviceDetection.isTouchDevice()) ? 'touchstart' : 'click';
        const openEvent = (DeviceDetection.isTouchDevice()) ? 'touchstart' : 'mouseenter';
        const closeEvent = (DeviceDetection.isTouchDevice()) ? 'touchstart' : 'mouseleave';

        // register opening triggers
        Iterator.iterate(this._triggerEls, el => {
            const flyoutId = DomAccess.getDataAttribute(el, this.options.triggerDataAttribute);
            el.addEventListener(clickEvent, this._openFlyoutById.bind(this, flyoutId, el));
            el.addEventListener(clickEvent, () => this._debounce(this._closeAllFlyouts));
        });

        // register closing triggers
        Iterator.iterate(this._closeEls, el => {
            el.addEventListener(clickEvent, this._closeAllFlyouts.bind(this));
        });

        // register non touch events for open flyouts
        if (!DeviceDetection.isTouchDevice()) {
            Iterator.iterate(this._flyoutEls, el => {
                el.addEventListener('mousemove', () => this._clearDebounce());
                el.addEventListener('mouseleave', () => this._debounce(this._closeAllFlyouts));
            });
        }
    }
}