import template from './sw-cms-preview-cbax-lexicon-navigation.html.twig';
import './sw-cms-preview-cbax-lexicon-navigation.scss';

const { Component } = Shopware;

Component.register('sw-cms-preview-cbax-lexicon-navigation', {
    template,

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        }
    }
});
