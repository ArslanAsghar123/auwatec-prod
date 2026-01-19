import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class Accordion extends Plugin {
    static options = {
        showClass: 'show',
        triggerSelector: '[data-accordion-trigger]',
        contentSelector: '[data-accordion-content]',
    };

    init() {
        this.trigger = DomAccess.querySelector(this.el, this.options.triggerSelector);
        this.content = DomAccess.querySelector(this.el, this.options.contentSelector);
        this.trigger.addEventListener('click', this._onClickTrigger.bind(this));
    }

    _onClickTrigger(event) {
        const showClass = this.options.showClass;

        new bootstrap.Collapse(this.content, {
            toggle: true,
        });

        this.content.addEventListener('shown.bs.collapse', () => {
            this.trigger.classList.add(showClass);
        });

        this.content.addEventListener('hidden.bs.collapse', () => {
            this.trigger.classList.remove(showClass);
        });
    }

}