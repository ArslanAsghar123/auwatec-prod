import template from './sw-cms-preview-cbax-lexicon-products.html.twig';
import './sw-cms-preview-cbax-lexicon-products.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-cbax-lexicon-products', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
