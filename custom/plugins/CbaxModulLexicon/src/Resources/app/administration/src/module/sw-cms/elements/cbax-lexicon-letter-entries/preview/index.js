import template from './sw-cms-el-preview-cbax-lexicon-letter-entries.html.twig';
import './sw-cms-el-preview-cbax-lexicon-letter-entries.scss';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-cbax-lexicon-letter-entries', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
