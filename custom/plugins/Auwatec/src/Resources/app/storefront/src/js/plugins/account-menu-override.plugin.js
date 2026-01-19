import AccountMenuPlugin from 'src/plugin/header/account-menu.plugin';

/**
 * Bugfix: event 'touchstart' closes the menu a split second after opening
 */
export default class AccountMenuOverride extends AccountMenuPlugin {
    _registerEventListeners() {
        const event = 'click';
        this.el.addEventListener(event, this._onClickAccountMenuTrigger.bind(this, this.el));

        document.addEventListener('Viewport/hasChanged', this._onViewportHasChanged.bind(this));
    }
}