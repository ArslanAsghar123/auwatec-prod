import ViewportDetection from 'src/helper/viewport-detection.helper';

export default class CbaxModulLexiconSearchSuggestPlugin extends window.PluginBaseClass {

    /**
     * init the plugin
     */
    init() {
        this._registerEvents();
    }

    _registerEvents() {
        this.$emitter.subscribe('afterSuggest', this._initSuggestContainer.bind(this));
    }

    _initSuggestContainer() {
        let newAbsoluteLeft;
        if (ViewportDetection.isSM() || ViewportDetection.isMD() || ViewportDetection.isLG() || ViewportDetection.isXL() || ViewportDetection.isXXL()) {
            const suggestContainer = this.el.querySelector('.cbax-lexicon-search-suggest-container');

            if (suggestContainer) {

                const bodyData = document.body.getBoundingClientRect();
                const searchFormdata = this.el.getBoundingClientRect();
                const suggestContainerData = suggestContainer.getBoundingClientRect();

                if (ViewportDetection.isSM() || ViewportDetection.isMD()) {
                    suggestContainer.style.width = bodyData.width -2 + 'px';
                    suggestContainer.style.left =  (-1) * suggestContainerData.left + 'px';

                } else {
                    suggestContainer.style.transform = 'unset';
                    suggestContainer.style.margin = 0;
                    const searchSuggestWidth = parseInt(suggestContainer.getAttribute('data-cbax-search-suggest-width'), 10);
                    if (searchSuggestWidth >= bodyData.width) {
                        suggestContainer.style.width = bodyData.width -2 + 'px';
                        newAbsoluteLeft = 1;
                    } else {
                        suggestContainer.style.width = searchSuggestWidth + 'px';
                        newAbsoluteLeft = (bodyData.width - searchSuggestWidth) / 2;
                    }

                    suggestContainer.style.left = (newAbsoluteLeft - searchFormdata.left) + 'px';
                }
            }
        }

    }
}