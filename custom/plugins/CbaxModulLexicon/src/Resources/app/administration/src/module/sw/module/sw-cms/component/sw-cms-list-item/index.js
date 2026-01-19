import template from './sw-cms-list-item.html.twig';
import './sw-cms-list-item.scss';

const { Component } = Shopware;

Component.override('sw-cms-list-item', {
    template,

    computed: {

        statusClasses() {
            if (this.page.cbaxAssigned !== undefined && this.page.cbaxAssigned > 0) {
                return {
                    'is--active': true
                };
            } else {
                return this.$super('statusClasses');
            }
        },

        defaultLayoutAsset() {

            if (this.page.type === 'cbax_lexicon') {
                return `url(${this.assetFilter(`administration/static/img/cms/default_preview_product_list.jpg`)})`;

            } else {
                return this.$super('defaultLayoutAsset');
            }
        },

        defaultItemLayoutAssetBackground() {

            if (this.page.sections.length < 1) {
                return null;
            }

            if (this.page.type === 'cbax_lexicon') {
                const path = 'administration/static/img/cms';

                return `url(${this.assetFilter(`${path}/preview_landingpage_${this.page.sections[0].type}.png`)})`;

            } else {
                return this.$super('defaultItemLayoutAssetBackground');
            }
        }
    }
});
