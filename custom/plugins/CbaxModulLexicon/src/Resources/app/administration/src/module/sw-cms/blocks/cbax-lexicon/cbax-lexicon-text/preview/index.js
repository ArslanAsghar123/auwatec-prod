import template from './sw-cms-preview-cbax-lexicon-text.html.twig';
import './sw-cms-preview-cbax-lexicon-text.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-cbax-lexicon-text', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
