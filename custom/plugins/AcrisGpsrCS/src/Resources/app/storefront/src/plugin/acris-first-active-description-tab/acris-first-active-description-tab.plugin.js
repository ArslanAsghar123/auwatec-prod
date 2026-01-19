import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class AcrisFirstActiveDescriptionTabPlugin extends Plugin {

    static options = {
        closestParentSelector: '.card-tabs',
    };

    init() {
        this.tabNavItems = DomAccess.querySelectorAll(this.el, '.product-detail-tab-navigation-link', false);
        this.elClosestParent = this.el.closest(this.options.closestParentSelector);
        this.tabContents = DomAccess.querySelectorAll(this.elClosestParent, '.tab-pane', false);
        if (this.tabNavItems && this.tabContents) {
            this.setFirstTabActive();
        }
    };

    setFirstTabActive() {
        let isFirst = true;
        this.setActiveId = null;
        this.setActiveTab = null;
        this.tabNavItems.forEach((tabNavItem) => {
            if (isFirst) {
                this.setActiveId = tabNavItem.id;
                this.setActiveTab = tabNavItem;
                if (!tabNavItem.classList.contains('active')) {
                    tabNavItem.classList.add('active');
                    tabNavItem.classList.add('show');
                    tabNavItem.setAttribute('aria-selected', true);
                }
                isFirst = false;
            } else {
                tabNavItem.classList.remove('active');
                tabNavItem.classList.remove('show');

                tabNavItem.setAttribute('aria-selected', false);
            }
        });
        isFirst = true;
        this.tabContents.forEach((tabNavContent) => {
            if (tabNavContent.getAttribute('aria-labelledby') === this.setActiveId && isFirst) {
                if (!tabNavContent.classList.contains('active')) {
                    tabNavContent.classList.add('active');
                    let containsSlider = DomAccess.querySelector(tabNavContent, '.base-slider', false);
                    if (containsSlider) {
                        this.resizeSlider();
                    }
                }
                isFirst = false;
            } else {
                tabNavContent.classList.remove('active');
            }
        })
    }

    /**
     * helper method to resize slider
     */
    resizeSlider() {
        this.setActiveTab.dispatchEvent(new Event('shown.bs.tab'));
    }

}
