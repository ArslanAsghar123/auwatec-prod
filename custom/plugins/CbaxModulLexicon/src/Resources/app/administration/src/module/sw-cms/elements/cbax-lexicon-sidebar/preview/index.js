import template from './sw-cms-el-preview-cbax-lexicon-sidebar.html.twig';
import './sw-cms-el-preview-cbax-lexicon-sidebar.scss';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-cbax-lexicon-sidebar', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
