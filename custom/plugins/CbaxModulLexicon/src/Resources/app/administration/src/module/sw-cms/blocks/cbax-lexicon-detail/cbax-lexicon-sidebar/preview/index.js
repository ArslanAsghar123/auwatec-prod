import template from './sw-cms-preview-cbax-lexicon-sidebar.html.twig';
import './sw-cms-preview-cbax-lexicon-sidebar.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-cbax-lexicon-sidebar', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
