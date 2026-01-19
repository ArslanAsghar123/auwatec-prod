import template from './sw-cms-el-preview-cbax-lexicon-navigation.html.twig';
import './sw-cms-el-preview-cbax-lexicon-navigation.scss';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-cbax-lexicon-navigation', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
