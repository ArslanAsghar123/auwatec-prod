import template from './sw-cms-preview-cbax-lexicon-content.html.twig';
import './sw-cms-preview-cbax-lexicon-content.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-cbax-lexicon-content', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
