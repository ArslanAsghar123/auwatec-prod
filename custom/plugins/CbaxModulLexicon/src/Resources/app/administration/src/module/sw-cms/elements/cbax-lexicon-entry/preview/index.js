import template from './sw-cms-el-preview-cbax-lexicon-entry.html.twig';
import './sw-cms-el-preview-cbax-lexicon-entry.scss';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-cbax-lexicon-entry', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
