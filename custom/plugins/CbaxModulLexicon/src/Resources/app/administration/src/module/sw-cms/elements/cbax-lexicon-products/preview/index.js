import template from './sw-cms-el-preview-cbax-lexicon-products.html.twig';
import './sw-cms-el-preview-cbax-lexicon-products.scss';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-cbax-lexicon-products', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
