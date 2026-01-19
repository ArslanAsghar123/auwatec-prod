import template from './sw-product-detail-specifications.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-product-detail-specifications', {
    template,

    data() {
        return {
            lexiconEntryIds: [],
            lexiconIsLoading: false
        };
    },

    computed: {
        lexiconEntryRepository() {
            return this.repositoryFactory.create('cbax_lexicon_entry');
        },

        lexiconEntryCriteria() {
            const criteria = new Criteria(1,50);
            criteria.addFilter(Criteria.not('AND', [Criteria.equals('listingType', 'product_stream')]));
            return criteria;
        }
    },

    created() {
        this.lexiconIsLoading = true;

        if (this.$route.params.id) {
            const initContainer = Shopware.Application.getContainer('init');
            const httpClient = initContainer.httpClient;
            const loginService = Shopware.Service('loginService');

            return httpClient.post('/cbax/lexicon/getLexiconProducts', {productId: this.$route.params.id}, {
                headers: {
                    Authorization: `Bearer ${loginService.getToken()}`,
                }
            }).then((result) => {
                if (result?.data?.success === true && Array.isArray(result.data.lexiconEntryIds)) {
                    this.lexiconEntryIds = result.data.lexiconEntryIds;
                }
                this.lexiconIsLoading = false;

            });
        }
    },

    methods: {
        onLexiconEntryAdd(event) {
            if (event.id) {
                this.changeLexiconEntry(event.id, 'add');
            }
        },

        onLexiconEntryRemove(event) {
            if (event.id) {
                this.changeLexiconEntry(event.id, 'rm');
            }
        },

        changeLexiconEntry(id, mode) {
            this.lexiconIsLoading = true;
            const initContainer = Shopware.Application.getContainer('init');
            const httpClient = initContainer.httpClient;
            const loginService = Shopware.Service('loginService');

            return httpClient.post('/cbax/lexicon/changeLexiconProducts',
                {
                    lexiconEntryId: id,
                    productId: this.product.id,
                    mode: mode
                },
                {
                    headers: {
                        Authorization: `Bearer ${loginService.getToken()}`,
                }
            }).then((result) => {
                this.lexiconIsLoading = false;
            });
        }
    }

});
