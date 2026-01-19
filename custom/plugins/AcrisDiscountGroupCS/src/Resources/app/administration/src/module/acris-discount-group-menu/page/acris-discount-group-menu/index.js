const { Mixin } = Shopware;
const { Component } = Shopware;

import template from './acris-discount-group-menu.html.twig';

Component.register('acris-discount-group-menu', {
    template,

    mixins: [
        Mixin.getByName('notification'),
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    }
});
