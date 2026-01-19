import template from './sw-cms-preview-cbax-lexicon-entry.html.twig';
import './sw-cms-preview-cbax-lexicon-entry.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-cbax-lexicon-entry', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
