export default class CbaxFocusSliderCrossSellingPlugin extends window.PluginBaseClass {

    init() {
        if (!window.Feature.isActive('ACCESSIBILITY_TWEAKS')) {
            this._initAccessibilityTweaks();
        }
    }

    _initAccessibilityTweaks() {
        this._setFocusAttributes();

        //slider weiter scrollen => neu init
        this.el.querySelector('.tns-slider').addEventListener('transitionend', () => {
            this._setFocusAttributes();
        })

        //bei Slider in Tabs, Tabswechsel => neu init
        this.el.addEventListener('rebuild', () => {
            this._setFocusAttributes();
        });
    }

    _setFocusAttributes() {
        let selectableElements, selectableEl, sliderItem;
        const sliderItems = this.el.querySelectorAll('.product-slider-item');

        for (sliderItem of sliderItems) {
            selectableElements = sliderItem.querySelectorAll('a, button, img');

            if (sliderItem.classList.contains('tns-slide-active')) {
                // Show selectable visible elements for focus
                for (selectableEl of selectableElements) {
                    selectableEl.removeAttribute('tabindex');
                }

            } else {
                // Hide selectable elements within cloned elements from focus
                for (selectableEl of selectableElements) {
                    selectableEl.setAttribute('tabindex', '-1');
                }
            }
        }
    }

}
