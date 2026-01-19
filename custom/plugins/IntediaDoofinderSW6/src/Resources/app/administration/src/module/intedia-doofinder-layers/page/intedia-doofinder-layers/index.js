const { Component } = Shopware;
const { Mixin } = Shopware;

import template from './intedia-doofinder-layers.html.twig';

Component.register('intedia-doofinder-layers', {
    template,
    inject: {
        communicationService: 'communication',
        repositoryFactory: 'repositoryFactory'
    },
    mixins: [
        Mixin.getByName('notification')
    ],

    created() {
        this.communicationService.getData().then((result) => {
            this.dataSource = result;

            result.forEach(function (item) {
                if (item.searchengine) {
                    this.communicationService.getProcess(item.id, item.doofinder_channel_id, item.doofinder_hash_id);
                }
            }, this);
        })
    },

    data: function () {
        return {
            dataSource: [],
            columns: [
                { property: 'domain', label: 'intedia-doofinder-layers.table.domain' },
                {
                    property: 'searchengine',
                    inlineEdit: 'boolean',
                    label: 'intedia-doofinder-layers.table.searchengine'
                },
                { property: 'doofinder_store_name', label: 'intedia-doofinder-layers.table.storeName' },
                { property: 'doofinderSearchEngine', label: 'intedia-doofinder-layers.table.doofinderSearchEngine' },
                { property: 'doofinder_hash_id', label: 'intedia-doofinder-layers.table.doofinderHashId' },
                { property: 'doofinder_status_message', label: 'intedia-doofinder-layers.table.doofinderStatus' }
            ],
            showDeleteModal: false,
            showLinkModal: false,
            showCreateModal: false,
        }
    },

    computed: {
        entityRepository() {
            return this.communicationService.getData();
        },
        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        }
    },

    methods: {
        onLink(id) {
            this.showLinkModal = id;
        },
        onLinkChange() {
            let loadingGif = document.getElementsByClassName('loadingGif')[0];
            loadingGif.style.display = 'block';

            let doofinderStoreId = document.querySelector("[id*='linkDoofinderStoreId']").value;
            this.communicationService.getStores().then((result) => {
                let searchEngineList = document.getElementById("doofinderHashId");
                searchEngineList.innerHTML = '';

                result.forEach(element => {
                    if (doofinderStoreId == element.id) {
                        for (let n = 0; n < element.searchEngines.length; n++) {
                            let crEl = document.createElement("option");
                            crEl.textContent = element.searchEngines[n].name;
                            crEl.value = element.searchEngines[n].id;
                            searchEngineList.appendChild(crEl);
                        }
                    }
                });

                loadingGif.style.display = 'none';
            });
        },
        onCloseLinkModal() {
            this.showLinkModal = false;
        },
        onConfirmLink(id, storefrontChannelId) {
            let doofinderStoreId = document.querySelector("[id*='linkDoofinderStoreId']").value;
            let hashId = document.querySelector("[id*='doofinderHashId']").value;
            let loadingGif = document.getElementsByClassName('loadingGif')[0];
            loadingGif.style.display = 'block';

            if (!doofinderStoreId || !hashId) {
                this.createNotificationError({
                    title: this.$tc('intedia-doofinder-layers.notification.error.title'),
                    message: this.$tc('intedia-doofinder-layers.notification.error.pleaseSelect')
                });

                loadingGif.style.display = 'none';
            } else {
                this.showLinkModal = false;

                this.communicationService.linkData(id, storefrontChannelId, doofinderStoreId, hashId).then(
                    (result) => {
                        if (result.error) {
                            this.createNotificationError({
                                title: this.$tc('intedia-doofinder-layers.notification.error.title'),
                                message: this.$tc('intedia-doofinder-layers.notification.error.' + result.error)
                            });
                        } else {
                            this.communicationService.getData().then((result) => {
                                this.dataSource = result;
                                this.createNotificationSuccess({
                                    title: this.$tc('intedia-doofinder-layers.notification.success.title'),
                                    message: this.$tc('intedia-doofinder-layers.notification.success.linkSearchEngine')
                                });
                            })
                        }

                        loadingGif.style.display = 'none';
                    }
                );
            }
        },


        onCreateModal(id) {
            this.showCreateModal = id;
        },
        onCloseCreateModal() {
            this.showCreateModal = false;
        },
        onConfirmCreateModal(id, storefrontChannelId) {
            let doofinderStoreId = document.querySelector("[id*='createDoofinderStoreId']").value;

            this.communicationService.createSearchEngine(id, storefrontChannelId, doofinderStoreId).then(
                (result) => {
                    if (result.error) {
                        this.createNotificationError({
                            title: this.$tc('intedia-doofinder-layers.notification.error.title'),
                            message: this.$tc('intedia-doofinder-layers.notification.error.' + result.error)
                        });
                    } else {
                        this.communicationService.getData().then((result) => {
                            this.dataSource = result;
                            this.createNotificationSuccess({
                                title: this.$tc('intedia-doofinder-layers.notification.success.title'),
                                message: this.$tc('intedia-doofinder-layers.notification.success.createSearchEngine')
                            });
                        })
                    }
                }
            );
            this.showCreateModal = false;
        },

        deleteLink(id) {
            this.communicationService.deleteSearchEngineLink(id)
                .then((result) => {
                    this.createNotificationSuccess({
                        title: this.$tc('intedia-doofinder-layers.notification.success.title'),
                        message: this.$tc('intedia-doofinder-layers.notification.success.deleteLink')
                    });
                    this.communicationService.getData().then((result) => {
                        this.dataSource = result;
                    });
                });
        },


        onDelete(id) {
            this.showDeleteModal = id;
        },
        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },
        onConfirmDelete(domainId, doofinder_channel_id) {
            this.showDeleteModal = false;

            this.salesChannelRepository
                .get(doofinder_channel_id, Shopware.Context.api)
                .then((result) => {
                    if (result !== null) {
                        this.salesChannelRepository
                            .delete(doofinder_channel_id, Shopware.Context.api)
                            .then(() => {
                                this.communicationService.deleteData(domainId).then(() => {
                                    this.communicationService.getData().then((result) => {
                                        this.dataSource = result;
                                        this.createNotificationSuccess({
                                            title: this.$tc('intedia-doofinder-layers.notification.success.title'),
                                            message: this.$tc('intedia-doofinder-layers.notification.success.deleteSearchEngine')
                                        });
                                    })
                                });
                            });
                    } else {
                        this.communicationService.deleteData(domainId).then(() => {
                            this.communicationService.getData().then((result) => {
                                this.dataSource = result;
                                this.createNotificationSuccess({
                                    title: this.$tc('intedia-doofinder-layers.notification.success.title'),
                                    message: this.$tc('intedia-doofinder-layers.notification.success.deleteSearchEngine')
                                });
                            })
                        });
                    }
                });
        },
        onProcessIndex(domainId, doofinderChannelId, doofinderHashId) {
            this.communicationService.processIndex(domainId, doofinderChannelId, doofinderHashId);
            this.createNotificationSuccess({
                title: this.$tc('intedia-doofinder-layers.notification.success.title'),
                message: this.$tc('intedia-doofinder-layers.notification.success.indexProcess')
            });
        },

        getList() {
            return this.entityRepository
                .then((result) => {
                    this.dataSource = result;
                });
        },
        viewSearchEngine(doofinder_store_id, doofinder_hash_id) {
            window.open('https://admin.doofinder.com/admin/store/searchengine/stats/searches?store_id=' +
                doofinder_store_id +
                '&hashid=' +
                doofinder_hash_id,'_blank');
        },
        enableRecommendations(doofinder_store_id, doofinder_hash_id) {
            window.open('https://admin.doofinder.com/admin/store/searchengine/recommendations?store_id=' +
                doofinder_store_id +
                '&hashid=' +
                doofinder_hash_id,'_blank');
        }
    }
});