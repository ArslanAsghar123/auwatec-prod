const { Mixin } = Shopware;
const { Component } = Shopware;

import template from './acris-gpsr-index.html.twig';

Component.register('acris-gpsr-index', {
    template,

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    }
});
