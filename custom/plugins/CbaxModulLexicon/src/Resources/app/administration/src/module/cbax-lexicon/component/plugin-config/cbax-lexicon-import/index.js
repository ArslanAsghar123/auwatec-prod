import template from './cbax-lexicon-import.html.twig';
import './cbax-lexicon-import.scss';

Shopware.Component.register('cbax-lexicon-import', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Shopware.Mixin.getByName('notification')
    ],

    props: {

    },

    created() {
    },

    watch: {

    },

    data() {
        return {
            importResult: [
                { name: 'header', label: this.$tc('cbax-lexicon.config.resultGrid.lexiconGeneral'), value: '', sum: false },
                { name: 'totalLexiconEntries', label: this.$tc('cbax-lexicon.config.resultGrid.totalLexiconEntries'), value: '-', sum: false },
                { name: 'countExistingLexiconEntries', label: this.$tc('cbax-lexicon.config.resultGrid.countExistingLexiconEntries'), value: '-', sum: false },
                { name: 'createdLexiconEntries', label: this.$tc('cbax-lexicon.config.resultGrid.createdLexiconEntries'), value: '-', sum: true },
                { name: 'spacer', label: '', value: '', sum: false },

                { name: 'header', label: this.$tc('cbax-lexicon.config.resultGrid.streamsGeneral'), value: '', sum: false },
                { name: 'totalSw5StreamsConnected', label: this.$tc('cbax-lexicon.config.resultGrid.totalSw5StreamsConnected'), value: '-', sum: true },
                { name: 'totalSw6MappedStreamConnections', label: this.$tc('cbax-lexicon.config.resultGrid.totalSw6MappedStreamConnections'), value: '-', sum: true },
                //{ name: 'totalSw5StreamConnections', label: this.$tc('cbax-lexicon.config.resultGrid.totalSw5StreamConnections'), value: '-', sum: true },
                //{ name: 'countSw6StreamsConnected', label: this.$tc('cbax-lexicon.config.resultGrid.countSw6StreamsConnected') + ' ' + this.$tc('cbax-lexicon.config.resultGrid.maxLikeEntries'), value: '-', sum: true },
                //{ name: 'countNotCreatableStreams', label: this.$tc('cbax-lexicon.config.resultGrid.countNotCreatable') + ' ' + this.$tc('cbax-lexicon.config.resultGrid.maxLikeEntries'), value: '-', sum: true },
                { name: 'spacer', label: '', value: '', sum: false },

                { name: 'header', label: this.$tc('cbax-lexicon.config.resultGrid.productsGeneral'), value: '', sum: false },
                { name: 'totalSw5Products', label: this.$tc('cbax-lexicon.config.resultGrid.totalSw5Products'), value: '-', sum: true },
                { name: 'totalSw6ProductsFound', label: this.$tc('cbax-lexicon.config.resultGrid.totalSw6ProductsFound'), value: '-', sum: true },
                //{ name: 'totalLexiconProducts', label: this.$tc('cbax-lexicon.config.resultGrid.totalLexiconProducts'), value: '-', sum: false },
                //{ name: 'countAlreadyExistingAssignments', label: this.$tc('cbax-lexicon.config.resultGrid.countAlreadyExistingAssignments'), value: '-', sum: true },
                //{ name: 'createdLexiconProducts', label: this.$tc('cbax-lexicon.config.resultGrid.createdLexiconProducts'), value: '-', sum: true },
                //{ name: 'countNotCreatableProduct', label: this.$tc('cbax-lexicon.config.resultGrid.countNotCreatable'), value: '-', sum: true },
                { name: 'spacer', label: '', value: '', sum: false },

                { name: 'header', label: this.$tc('cbax-lexicon.config.resultGrid.shopsGeneral'), value: '', sum: false },
                { name: 'totalSw5Shops', label: this.$tc('cbax-lexicon.config.resultGrid.totalSw5Shops'), value: '-', sum: true },
                { name: 'totalSw6ShopsFound', label: this.$tc('cbax-lexicon.config.resultGrid.totalSw6ShopsFound'), value: '-', sum: true },
                //{ name: 'totalLexiconShops', label: this.$tc('cbax-lexicon.config.resultGrid.totalLexiconShops'), value: '-', sum: false },
                //{ name: 'countAlreadyExistingShopAssignments', label: this.$tc('cbax-lexicon.config.resultGrid.countAlreadyExistingShopAssignments'), value: '-', sum: true },
                //{ name: 'createdLexiconSalesChannel', label: this.$tc('cbax-lexicon.config.resultGrid.createdLexiconSalesChannel'), value: '-', sum: true },
                //{ name: 'countNotCreatableSalesChannel', label: this.$tc('cbax-lexicon.config.resultGrid.countNotCreatable'), value: '-', sum: true },
            ],
            showImportResult: false,
            isLoading: false
        };
    },

    computed: {
        resultColumns() {
            return this.getResultColumns();
        },
    },

    methods: {
        resetImportResult() {
            this.importResult.forEach((item) => {
                item.value = '-';
                if (item.name === 'spacer' || item.name === 'header') {
                     item.value = '';
                }
            })
        },
        importEntries(start) {
            this.isLoading = true;
            const limit = 1000;

            if (start === 0) {
                this.resetImportResult();
            }

            try {
                const initContainer = Shopware.Application.getContainer('init');
                const httpClient = initContainer.httpClient;
                const loginService = Shopware.Service('loginService');

                httpClient.post('cbax/lexicon/importData', {start: start, limit: limit}, {
                    headers: {
                        Authorization: `Bearer ${loginService.getToken()}`,
                    }
                }).then((responseButtonClick) => {
                    if (responseButtonClick.data !== undefined) {
                        let msg = '';

                        if (responseButtonClick.data['msg'] !== undefined) {
                            msg = this.$tc(responseButtonClick.data['msg']);
                        }

                        if (responseButtonClick.data['success'] === true) {
                            if (msg === '') {
                                msg = this.$tc('cbax-lexicon.config.button.successMessage');
                            }

                            if (responseButtonClick.data['successData'] !== undefined) {
                                this.onSuccessImport(responseButtonClick.data['successData'], start);
                                if (responseButtonClick.data['successData']['totalLexiconEntries'] > start + limit) {
                                    if (start + limit > 4000) {
                                        this.createNotificationError({
                                            title: this.$tc('cbax-lexicon.config.button.failure'),
                                            message: this.$tc('cbax-lexicon.config.notification.errorToManyEntries')
                                        });
                                    } else {
                                        this.importEntries(start + limit);
                                    }
                                } else {
                                    this.createNotificationSuccess({
                                        title: this.$tc('cbax-lexicon.config.button.success'),
                                        message: msg
                                    });

                                    this.getProductAssignments(0);
                                }
                            } else {
                                this.isLoading = false;

                                this.createNotificationError({
                                    title: this.$tc('cbax-lexicon.config.button.failure'),
                                    message: msg
                                });
                            }
                        } else {
                            if (msg === '') {
                                msg = this.$tc('cbax-lexicon.config.button.errorMessage');
                            } else if (responseButtonClick.data['errorData'] !== undefined) {
                                msg = msg + '(' + responseButtonClick.data['errorData'] + ')'
                            }

                            this.createNotificationError({
                                title: this.$tc('cbax-lexicon.config.button.failure'),
                                message: msg
                            });

                            this.isLoading = false;
                        }
                    }
                }).catch((err) => {
                    this.isLoading = false;

                    this.createNotificationError({
                        title: this.$tc('cbax-lexicon.config.button.failure'),
                        message: this.$tc('cbax-lexicon.config.button.errorRoute'),
                    });
                });
            } catch (ex) {
                this.isLoading = false;

                this.createNotificationError({
                    title: this.$tc('cbax-lexicon.config.button.failure'),
                    message: this.$tc('cbax-lexicon.config.button.errorRoute')
                });
            }
        },
        onSuccessImport(data, start) {
            this.importResult.forEach((stat, index) => {
                if (data.hasOwnProperty(stat.name)) {
                    if (this.importResult[index].value !== '-' && this.importResult[index].sum === true) {
                        this.importResult[index].value += data[stat.name];
                    } else if (start === 0) {
                        this.importResult[index].value = data[stat.name];
                    }
                }
            })
            this.showImportResult = true;
        },
        getResultColumns() {
            return [{
                property: 'label',
                label: '',
                align: 'left',
                allowResize: true
            }, {
                property: 'value',
                label: '',
                allowResize: true,
                align: 'right'
            }];
        },
        getProductAssignments(start = 0) {
            const limit = 500;

            const initContainer = Shopware.Application.getContainer('init');
            const httpClient = initContainer.httpClient;
            const loginService = Shopware.Service('loginService');

            httpClient.post('cbax/lexicon/importProductAssignments', {start: start, limit: limit}, {
                headers: {
                    Authorization: `Bearer ${loginService.getToken()}`,
                }
            }).then((responseProductAssignments) => {
                if (responseProductAssignments.data !== undefined) {
                    let msg = '';

                    if (responseProductAssignments.data['msg'] !== undefined) {
                        msg = this.$tc(responseProductAssignments.data['msg']);
                    }

                    if (responseProductAssignments.data['success'] === true && responseProductAssignments.data['successData'] !== undefined) {
                        this.onSuccessImport(responseProductAssignments.data['successData'], start);

                        if (responseProductAssignments.data['successData']['totalLexiconProducts'] > start + limit) {
                            return this.getProductAssignments(start + limit);
                        } else {
                            this.createNotificationSuccess({
                                title: this.$tc('cbax-lexicon.config.button.success'),
                                message: msg
                            });

                            this.getShopAssignments();
                        }
                    } else {
                        if (msg === '') {
                            msg = this.$tc('cbax-lexicon.config.button.errorMessage');
                        } else if (responseProductAssignments.data['errorData'] !== undefined) {
                            msg = msg + '(' + responseProductAssignments.data['errorData'] + ')'
                        }

                        this.createNotificationError({
                            title: this.$tc('cbax-lexicon.config.button.failure'),
                            message: msg
                        });

                        this.isLoading = false;
                    }
                }
            }).catch((err) => {
                this.isLoading = false;

                this.createNotificationError({
                    title: this.$tc('cbax-lexicon.config.button.failure'),
                    message: this.$tc('cbax-lexicon.config.notification.errorProductAssignments'),
                });
            });
        },
        getShopAssignments(start = 0) {
            const limit = 500;

            const initContainer = Shopware.Application.getContainer('init');
            const httpClient = initContainer.httpClient;
            const loginService = Shopware.Service('loginService');

            httpClient.post('cbax/lexicon/importShopAssignments', {start: start, limit: limit}, {
                headers: {
                    Authorization: `Bearer ${loginService.getToken()}`,
                }
            }).then((responseShopAssignments) => {
                if (responseShopAssignments.data !== undefined) {
                    let msg = '';

                    if (responseShopAssignments.data['msg'] !== undefined) {
                        msg = this.$tc(responseShopAssignments.data['msg']);
                    }

                    if (responseShopAssignments.data['success'] === true && responseShopAssignments.data['successData'] !== undefined) {
                        this.onSuccessImport(responseShopAssignments.data['successData'], start);

                        if (responseShopAssignments.data['successData']['totalLexiconShops'] > start + limit) {
                            return this.getShopAssignments(start + limit);
                        } else {
                            this.createNotificationSuccess({
                                title: this.$tc('cbax-lexicon.config.button.success'),
                                message: msg
                            });
                        }
                    } else {
                        if (msg === '') {
                            msg = this.$tc('cbax-lexicon.config.button.errorMessage');
                        } else if (responseShopAssignments.data['errorData'] !== undefined) {
                            msg = msg + '(' + responseShopAssignments.data['errorData'] + ')'
                        }

                        this.createNotificationError({
                            title: this.$tc('cbax-lexicon.config.button.failure'),
                            message: msg
                        });

                        this.isLoading = false;
                    }
                }
            }).catch((err) => {
                this.createNotificationError({
                    title: this.$tc('cbax-lexicon.config.button.failure'),
                    message: this.$tc('cbax-lexicon.config.notification.errorShopAssignments'),
                });
            }).finally(() => {
                this.isLoading = false;
            });
        },
    }
});
