import template from './sw-cms-list.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-cms-list', {
    template,

    computed: {
        systemConfigRepository() {
            return this.repositoryFactory.create('system_config');
        }
    },

    methods: {
        //overwrite
        createdComponent() {
            this.getLConfigCMSPages();

            //register new cms page type
            if (this.cmsPageTypeService.getType('cbax_lexicon') === undefined) {
                const newTypeData = {
                    name: 'cbax_lexicon',
                    icon: 'regular-books',
                    title: 'cbax-lexicon.create.labelLexicon',
                    hideInList: false
                };
                this.cmsPageTypeService.register(newTypeData);
            }

            this.$super('createdComponent');
        },

        //overwrite
        async getList() {
            let allCMSPages = await this.$super('getList');
            this.isLoading = true;

            //eigene properties für Verwendung der layouts setzen
            if (this.pages.length > 0) {
                this.lConfigPages.forEach((id) => {
                    this.pages.forEach((page) => {
                        if (page.type === 'cbax_lexicon' && page.id === id) {
                            if (page.cbaxAssigned !== undefined) {
                                page.cbaxAssigned++;
                            } else {
                                page.cbaxAssigned = 1;
                            }
                            if (page.id === this.lDefaultCMSPages.defaultDetail) {
                                page.cbaxDefaultDetail = true;
                            }
                            if (page.id === this.lDefaultCMSPages.defaultIndex) {
                                page.cbaxDefaultIndex = true;
                            }
                            if (page.id === this.lDefaultCMSPages.defaultListing) {
                                page.cbaxDefaultListing = true;
                            }
                            if (page.id === this.lDefaultCMSPages.defaultContent) {
                                page.cbaxDefaultContent = true;
                            }
                        }
                    });
                });
            }
            allCMSPages = this.pages;

            this.$nextTick(() => {
                this.isLoading = false;
                return allCMSPages;
            });
        },

        //overwrite
        getPageProductCount(page) {
            if (page.cbaxAssigned !== undefined) {
                return page.cbaxAssigned;
            } else {
                return this.$super('getPageProductCount', page);
            }
        },

        //new, liefert verwendete CMS Pages in Plugin Setting
        getLConfigCMSPages() {
            this.lConfigPages = [];
            this.lDefaultCMSPages = {
                defaultIndex: null,
                defaultDetail: null,
                defaultListing: null,
                defaultContent: null
            };
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equalsAny('configurationKey',
                [
                    'CbaxModulLexicon.config.cmsPageDetail',
                    'CbaxModulLexicon.config.cmsPageIndex',
                    'CbaxModulLexicon.config.cmsPageListing',
                    'CbaxModulLexicon.config.cmsPageContent',
                ]
            ));
            this.systemConfigRepository.search(criteria).then((result) => {
                if (result) {
                    result.forEach((sc) => {
                        this.lConfigPages.push(sc.configurationValue);
                        if (!sc.salesChannelId && sc.configurationKey === 'CbaxModulLexicon.config.cmsPageDetail') {
                            this.lDefaultCMSPages.defaultDetail = sc.configurationValue;
                        }
                        if (!sc.salesChannelId && sc.configurationKey === 'CbaxModulLexicon.config.cmsPageIndex') {
                            this.lDefaultCMSPages.defaultIndex = sc.configurationValue;
                        }
                        if (!sc.salesChannelId && sc.configurationKey === 'CbaxModulLexicon.config.cmsPageListing') {
                            this.lDefaultCMSPages.defaultListing = sc.configurationValue;
                        }
                        if (!sc.salesChannelId && sc.configurationKey === 'CbaxModulLexicon.config.cmsPageContent') {
                            this.lDefaultCMSPages.defaultContent = sc.configurationValue;
                        }
                    });
                }
            });
        }
    }

});
