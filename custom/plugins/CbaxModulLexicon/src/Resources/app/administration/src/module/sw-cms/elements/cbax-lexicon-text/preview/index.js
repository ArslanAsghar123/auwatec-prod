import template from './sw-cms-el-preview-cbax-lexicon-text.html.twig';
import './sw-cms-el-preview-cbax-lexicon-text.scss';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-cbax-lexicon-text', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
