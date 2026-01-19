import template from './sw-cms-el-preview-cbax-lexicon-content.html.twig';
import './sw-cms-el-preview-cbax-lexicon-content.scss';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-cbax-lexicon-content', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
