import template from './sw-cms-preview-cbax-lexicon-popular-entries.html.twig';
import './sw-cms-preview-cbax-lexicon-popular-entries.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-cbax-lexicon-popular-entries', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
