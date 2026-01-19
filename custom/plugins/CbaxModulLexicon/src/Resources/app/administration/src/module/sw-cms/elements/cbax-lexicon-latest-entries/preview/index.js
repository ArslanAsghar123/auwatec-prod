import template from './sw-cms-el-preview-cbax-lexicon-latest-entries.html.twig';
import './sw-cms-el-preview-cbax-lexicon-latest-entries.scss';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-cbax-lexicon-latest-entries', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
