import template from './sw-cms-create-wizard.html.twig';

const { Component } = Shopware;

Component.override('sw-cms-create-wizard', {
    template,

    computed: {

        pagePreviewMedia() {

            if (this.page.sections.length < 1) {
                return '';
            }

            if (this.page.type === 'cbax_lexicon') {
                const imgPath = 'administration/static/img/cms';

                return `url(${this.assetFilter(`${imgPath}/preview_landingpage_${this.page.sections[0].type}.png`)})`;

            } else {
                return this.$super('pagePreviewMedia');
            }
        }
    }
});
