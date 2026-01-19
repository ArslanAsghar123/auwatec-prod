import template from './cbax-lexicon-config-seo.html.twig';
import './cbax-lexicon-config-seo.scss';

import deDE from "../../snippet/de-DE";
import enGB from "../../snippet/en-GB";

const { Component } = Shopware;

Component.register('cbax-lexicon-config-seo', {
    template,

    mixins: [
        Shopware.Mixin.getByName('sw-inline-snippet'),
        Shopware.Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            showDeleteModal: false
        };
    },

    computed: {
        translatedLexiconConfigSeo() {
            this.snippets = {
                'de-DE': deDE,
                'en-GB': enGB
            };

            return this.getInlineSnippet(this.snippets);
        },

        routerLink() {
            return 'sw.settings.logging.index';
        }
    },

    methods: {

        onShowDeleteModal() {
            this.showDeleteModal = true;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onDeleteSeos() {
            this.isLoading = true;
            this.showDeleteModal = false;

            const initContainer = Shopware.Application.getContainer('init');
            const httpClient = initContainer.httpClient;
            const loginService = Shopware.Service('loginService');

            httpClient.get('/cbax/lexicon/seoDelete',
                {
                    headers: {
                        Authorization: `Bearer ${loginService.getToken()}`,
                    }
                }).then((response) => {
                if (response && response.data && response.data.success) {
                    this.createNotificationSuccess({
                        title: this.$tc('cbax-lexicon.seo.success'),
                        message: this.$tc('cbax-lexicon.seo.deleteSuccess')
                    });
                } else {
                    this.createNotificationError({
                        title: this.$tc('cbax-lexicon.seo.error'),
                        message: this.$tc('cbax-lexicon.seo.deleteError')
                    });
                }
                this.isLoading = false;
            }).catch((err) => {
                this.createNotificationError({
                    title: this.$tc('cbax-lexicon.seo.unknown'),
                    message: this.$tc('cbax-lexicon.seo.unknown')
                });
                this.isLoading = false;
            });

        },

        onGenerateSeos() {
            this.isLoading = true;

            const initContainer = Shopware.Application.getContainer('init');
            const httpClient = initContainer.httpClient;
            const loginService = Shopware.Service('loginService');

            let parameters = {};
            parameters.adminLocaleLanguage = Shopware.State.getters.adminLocaleLanguage + '-' + Shopware.State.getters.adminLocaleRegion;

            httpClient.get('/cbax/lexicon/seo',
                {
                    headers: {
                        Authorization: `Bearer ${loginService.getToken()}`,
                    },
                    params: parameters
                }).then((response) => {
                if (response && response.data && response.data.errors.length === 0) {
                    this.createNotificationSuccess({
                        title: this.$tc('cbax-lexicon.seo.success'),
                        message: response.data.message
                    });
                } else if (response && response.data && response.data.errors.length > 0) {
                    const route = {
                        name: this.routerLink
                    };
                    const loggingLink = this.$router.resolve(route);
                    this.createNotificationError({
                        title: this.$tc('cbax-lexicon.seo.error'),
                        message: response.data.message + '<br>' + response.data.errorMessage + '<br><a href="' + loggingLink.href + '">' + this.$tc('cbax-lexicon.seo.link') + '</a>'
                    });
                } else {
                    this.createNotificationError({
                        title: this.$tc('cbax-lexicon.seo.unknown'),
                        message: this.$tc('cbax-lexicon.seo.unknown') + ' 1'
                    });
                }
                this.isLoading = false;
            }).catch((err) => {
                this.createNotificationError({
                    title: this.$tc('cbax-lexicon.seo.unknown'),
                    message: this.$tc('cbax-lexicon.seo.unknown') + ' 2'
                });
                this.isLoading = false;
            });
        }
    }
});
