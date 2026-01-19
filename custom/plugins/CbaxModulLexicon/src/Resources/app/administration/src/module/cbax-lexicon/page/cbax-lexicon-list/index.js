import template from './cbax-lexicon-list.html.twig';
import './cbax-lexicon-list.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('cbax-lexicon-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

	mixins: [
		Mixin.getByName('listing'),
        Mixin.getByName('notification')
    ],

    shortcuts: {
        'SYSTEMKEY+G': 'onGenerateSeo'
    },

	metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    data() {
        return {
			entityName: 'cbax_lexicon_entry',
            entries: null,
            isLoading: false,
			total: 0,
			sortBy: 'title',
            sortDirection: 'ASC',
            naturalSorting: false,
            salesChannelFilters: [],
            filterSidebarIsOpen: false,
            internalFilters: {},
            prodCounts: [],
            productStreams: []
        };
    },

	created() {
		this.isLoading = false;
        this.setSalesChannelFilters();
	},

    computed: {
        routerLink() {
            return 'sw.settings.logging.index';
        },

		lexiconRepository() {
            return this.repositoryFactory.create('cbax_lexicon_entry');
        },

        productStreamRepository() {
            return this.repositoryFactory.create('product_stream');
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

		lexiconCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            this.naturalSorting = false;

            // Suche
            criteria.setTerm(this.term);
            // Sortierung
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            // Saleschannel und Produkte dazu laden
            criteria.addAssociation('saleschannels.salesChannel');

            return criteria;
        },

        salesChannelCriteria() {
		    // nur Shops Saleschannel anzeigen
            const defaultStorefrontId = '8A243080F92E4C719546314B577CF82B';
            const criteria = new Shopware.Data.Criteria();

            criteria.addFilter(Shopware.Data.Criteria.equals('typeId', defaultStorefrontId));

            return criteria;
        },

        productStreamCriteria() {
            return new Shopware.Data.Criteria();
        },

        lexiconColumns() {
            return [{
                property: 'title',
                dataIndex: 'title',
                label: this.$tc('cbax-lexicon.list.columnTitle'),
                routerLink: 'cbax.lexicon.detail',
                inlineEdit: 'string',
                allowResize: true
            }, {
                property: 'keyword',
                dataIndex: 'keyword',
                label: this.$tc('cbax-lexicon.list.columnKeyword'),
                routerLink: 'cbax.lexicon.detail',
                inlineEdit: 'string',
                allowResize: true
            }, {
                property: 'countProducts',
                dataIndex: 'countProducts',
                label: this.$tc('cbax-lexicon.list.columnCountProducts'),
                allowResize: true,
                sortable: false,
                align: 'center'
            }, {
                property: 'impressions',
                dataIndex: 'impressions',
                label: this.$tc('cbax-lexicon.list.columnImpressions'),
                allowResize: true,
                align: 'center'
            }, {
                property: 'active',
                dataIndex: 'active',
                label: this.$tc('cbax-lexicon.list.columnActive'),
                allowResize: true,
                sortable: false,
                align: 'center'
            }, {
                property: 'saleschannels',
                label: this.$tc('cbax-lexicon.list.columnSalesChannel'),
                allowResize: true,
                sortable: false,
            }, {
                property: 'productStream',
                label: this.$tc('cbax-lexicon.list.columnProductStream'),
                allowResize: true,
                sortable: false,
            }];
        },

        tooltipGenerateSeo() {
            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + G`,
                appearance: 'light'
            };
        }
    },

	methods: {
        async getLexiconEntries() {
            this.lexiconCriteria.filters = [];
            // Standard-Criteria um optionale Filter dynamisch ergänzen
            Object.values(this.internalFilters).forEach((item) => {
                this.lexiconCriteria.addFilter(item);
            });

            return this.lexiconRepository.search(this.lexiconCriteria).then((items) => {
                return items;
            })
        },

        async getProductListCounts() {
            const initContainer = Shopware.Application.getContainer('init');
            const httpClient = initContainer.httpClient;
            const loginService = Shopware.Service('loginService');

            // Ajax-Request an den Controller schicken
            return httpClient.get('/cbax/lexicon/getProductCountList', {
                headers: {
                    Authorization: `Bearer ${loginService.getToken()}`,
                }
            }).then((prodCounts) => {
                if (prodCounts && prodCounts.data !== undefined) {
                    return prodCounts.data;
                } else return [];
            });
        },

        async getList() {
            // Ladeeffekt für Änderungen
            this.isLoading = true;

            const entriesPromise = this.getLexiconEntries();
            const prodCountsPromise = this.getProductListCounts();
            const productStreamsPromise = this.getProductStreams();

            //Entries und alle Product Counts für nicht Product Stream Entries parallel laden
            let [entries, prodCounts, productStreams] = await Promise.all([entriesPromise, prodCountsPromise, productStreamsPromise]);
            this.prodCounts = prodCounts;
            this.entries = entries;
            this.total = entries.total;
            this.productStreams = productStreams;

            this.renderColumns();
        },

        // Saleschannels auf nur "Storefront" filtern
        setSalesChannelFilters() {
            this.salesChannelRepository.search(this.salesChannelCriteria).then((salesChannels) => {
                this.salesChannelFilters = salesChannels;
            });
        },

        getProductStreams() {
            return this.productStreamRepository.search(this.productStreamCriteria).then((productStreams) => {
                return productStreams;
            });
        },

        onChangeLanguage() {
            this.getList();
        },

        closeContent() {
            if (this.filterSidebarIsOpen) {
                this.$refs.filterSideBar.closeContent();
                this.filterSidebarIsOpen = false;
                return;
            }

            this.$refs.filterSideBar.openContent();
            this.filterSidebarIsOpen = true;
        },

        /**
         * die Group entspricht der Datenbank Spalte, die abgeglichen werden soll
         * bei einer Association z.B. so "saleschannels.salesChannelId"
         * @param filter im Format Booleanfilter {id: item.id, group: 'saleschannels.salesChannelId', value: $event}
         */
        onChange(filter) {
            if (filter === null) {
                filter = [];
            }

            this.handleBooleanFilter(filter);
            this.getList();
        },

        /**
         * Boolean filter auswerten und Änderungen setzen
         * @param filter
         */
        handleBooleanFilter(filter) {
            if (!Array.isArray(this[filter.group])) {
                this[filter.group] = [];
            }

            if (!filter.value) {
                this[filter.group] = this[filter.group].filter((x) => { return x !== filter.id; });

                if (this[filter.group].length > 0) {
                    this.internalFilters[filter.group] = Criteria.equalsAny(filter.group, this[filter.group]);
                } else {
                    delete this.internalFilters[filter.group];
                }

                return;
            }

            this[filter.group].push(filter.id);
            this.internalFilters[filter.group] = Criteria.equalsAny(filter.group, this[filter.group]);
        },

        renderColumns() {
            let prodStreamEntries = [];
            //Entries noch ohne count mit product stream sammeln, um alle counts mit einem Call zu holen
            this.entries.forEach( (entry, index) => {
                if (entry.countProducts === undefined && entry.listingType === 'product_stream' && entry.productStreamId !== undefined) {
                    prodStreamEntries.push({ id: entry.id, productStreamId: entry.productStreamId });
                }

                if (entry['productStreamId'] !== null) {
                    const foundStream = this.productStreams.find((stream) => {
                        return stream['id'] === entry['productStreamId']
                    })

                    if (foundStream !== undefined && foundStream !== null) {
                        entry.productStream = foundStream['name'];
                    } else {
                        entry.productStream = '';
                    }
                }
            });

            if (prodStreamEntries.length > 0) {
                const initContainer = Shopware.Application.getContainer('init');
                const httpClient = initContainer.httpClient;
                const loginService = Shopware.Service('loginService');

                httpClient.post('/cbax/lexicon/getProductCountStream', {prodStreamEntries: prodStreamEntries}, {
                    headers: {
                        Authorization: `Bearer ${loginService.getToken()}`,
                    }
                }).then((prodCounts) => {
                    if (prodCounts && prodCounts.data !== undefined) {
                        // Product Counts daten zusammenfassen
                        this.prodCounts = Object.assign(this.prodCounts, prodCounts.data);
                        //Counts zuweisen
                        this.entries.forEach( (entry, index) => {
                            if (entry.countProducts === undefined && this.prodCounts[entry.id] !== undefined) {
                                entry.countProducts = this.prodCounts[entry.id];
                            } else if (entry.countProducts === undefined) {
                                entry.countProducts = 0;
                            }
                        });

                        this.isLoading = false;
                    }
                }).catch((e) => {
                    this.isLoading = false;
                });
            } else {
                //Counts zuweisen, wenn der call nicht nötig war
                this.entries.forEach( (entry, index) => {
                    if (entry.countProducts === undefined && this.prodCounts[entry.id] !== undefined) {
                        entry.countProducts = this.prodCounts[entry.id];
                    } else if (entry.countProducts === undefined) {
                        entry.countProducts = 0;
                    }
                });

                this.isLoading = false;
            }
        },

        updateRecords(items) {
            this.isLoading = true;
            this.total = items.total;
            this.entries = items;
            this.renderColumns();
        },

        onGenerateSeo() {
            this.isLoading = true;

            // httpClient holen
            const initContainer = Shopware.Application.getContainer('init');
            const httpClient = initContainer.httpClient;
            const loginService = Shopware.Service('loginService');

            let parameters = {};
            parameters.adminLocaleLanguage = Shopware.State.getters.adminLocaleLanguage + '-' + Shopware.State.getters.adminLocaleRegion;

            // Ajax-Request an den Controller schicken
            httpClient.get('/cbax/lexicon/seo', {
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
        },

        async onDuplicate(referenceLexiconEntry) {
            const copyKeyword = referenceLexiconEntry.keyword.replaceAll('+', `${this.$tc('global.default.copy')}` + '+');
            const behavior = {
                overwrites: {
                    title: `${referenceLexiconEntry.title} ${this.$tc('global.default.copy')}`,
                    keyword: copyKeyword + `${this.$tc('global.default.copy')}`
                },
            };

            const clone = await this.lexiconRepository.clone(referenceLexiconEntry.id, behavior);

            this.$nextTick(() => {
                this.$router.push({ name: 'cbax.lexicon.detail', params: { id: clone.id } });
            });
        }

    }
});
