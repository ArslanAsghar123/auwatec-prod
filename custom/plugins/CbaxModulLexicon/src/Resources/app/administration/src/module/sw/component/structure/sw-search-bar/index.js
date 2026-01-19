import template from './sw-search-bar.html.twig';

//import deDE from "../../../snippet/de-DE";
//import enGB from "../../../snippet/en-GB";

Shopware.Component.override('sw-search-bar', {
    template,

    mixins: [
        Shopware.Mixin.getByName('sw-inline-snippet')
    ],

    computed: {
        translatedLexicon() {
            this.snippets = {
                'de-DE': deDE,
                'en-GB': enGB
            };

            return this.getInlineSnippet(this.snippets);
        }
    }
});
