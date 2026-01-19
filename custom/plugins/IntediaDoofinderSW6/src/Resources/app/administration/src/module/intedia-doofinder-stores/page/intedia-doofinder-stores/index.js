const { Component } = Shopware;
const { Mixin } = Shopware;

import template from './intedia-doofinder-stores.html.twig';
import './intedia-doofinder-stores.scss';

Component.register('intedia-doofinder-stores', {
    template,
    inject: {
        communicationService: 'communication',
        repositoryFactory: 'repositoryFactory'
    },
    mixins: [
        Mixin.getByName('notification')
    ],
    created() {
        this.communicationService.getStores().then((result) => {
            this.dataSource = result;
        })
    },

    data: function () {
        return {
            dataSource: [],
            columns: [
                { property: 'name', label: 'intedia-doofinder-stores.table.store' },
                { property: 'id', label: 'intedia-doofinder-stores.table.storeId' },
                { property: 'standardSearchEngine.name', label: 'intedia-doofinder-stores.table.standardSearchEngine'}
            ],
            showDeleteModal: false,
            showCreateModal: false,
            showEditModal: false,
        }
    },

    computed: {
        entityRepository() {
            return this.communicationService.getStores();
        },
    },

    methods: {
        onOpenCreateStoreModal() {
            this.showCreateModal = true;
            this.communicationService.getData().then((result) => {
                let createDomainSelect = document.getElementById("intediaDoofinder_domainId");

                let defaultValue = document.createElement("option");
                defaultValue.textContent = this.$tc('intedia-doofinder-stores.modals.create.fields.pleaseChoose');
                defaultValue.value = '';
                createDomainSelect.appendChild(defaultValue);

                for (let n = 0; n < result.length; n++) {
                    if (!result[n].searchengine) {
                        let crEl = document.createElement("option");
                        crEl.textContent = result[n].domain;
                        crEl.value = result[n].domain_id;
                        createDomainSelect.appendChild(crEl);
                    }
                }
            });
        },
        onCloseCreateStoreModal() {
            this.showCreateModal = false;
        },
        onSubmitCreateStoreModal()
        {
            let loadingGif = document.getElementsByClassName('loadingGif')[0];
            loadingGif.style.display = 'block';

            let title = document.querySelector("[id*='intediaDoofinder_title']").value;
            let trigger = document.querySelector("[id*='intediaDoofinder_trigger']").value;
            let domainId = document.querySelector("[id*='intediaDoofinder_domainId']").value;

            if (domainId == '') {
                this.createNotificationError({
                    title: this.$tc('intedia-doofinder-stores.notification.error.title'),
                    message: this.$tc('intedia-doofinder-stores.notification.error.emptyDomainId')
                });
                loadingGif.style.display = 'none';
            } else {
                this.communicationService.createStore(domainId, title, trigger
                ).then((result) => {
                    if (result.error) {
                        this.createNotificationError({
                            title: this.$tc('intedia-doofinder-stores.notification.error.title'),
                            message: this.$tc('intedia-doofinder-stores.notification.error.' + result.error)
                        });
                    } else {
                        this.communicationService.getStores().then((dataResult) => {
                            this.dataSource = dataResult;
                            this.showCreateModal = false;
                            this.createNotificationSuccess({
                                title: this.$tc('intedia-doofinder-stores.notification.success.title'),
                                message: this.$tc('intedia-doofinder-stores.notification.success.createStore')
                            });
                        })
                    }
                    loadingGif.style.display = 'none';
                });
            }
        },

        onOpenDeleteStoreModal(id) {
            this.showDeleteModal = id;
        },
        onCloseDeleteStoreModal() {
            this.showDeleteModal = false;
        },
        onConfirmDeleteStoreModal(storeId) {
            this.showDeleteModal = false;
            this.communicationService.deleteStore(storeId).then((result) => {
                this.communicationService.getStores().then((result) => {
                    this.dataSource = result;
                    this.createNotificationSuccess({
                        title: this.$tc('intedia-doofinder-stores.notification.success.title'),
                        message: this.$tc('intedia-doofinder-stores.notification.success.deleteStore')
                    });
                })
            });
        },

        onOpenEditStoreModal(id) {
            this.showEditModal = id;
            let data = this.communicationService.getStore(id);
            data.then(function (id, data) {
                document.getElementById("intediaDoofinder_title").value = data[0].name;

                if (data[0].config.trigger) {
                    document.getElementById("intediaDoofinder_trigger").value = data[0].config.trigger;
                }

                this.communicationService.getData().then((result) => {
                    let editDomainSelect = document.getElementById("intediaDoofinder_domainId_" + id);

                    for (var i = 0; i < result.length; i++) {
                        var opt = result[i].domain;
                        var el = document.createElement("option");
                        el.textContent = opt;
                        el.value = result[i].domain_id;
                        editDomainSelect.appendChild(el);
                        if (data[0].config.defaults) {
                            if (result[i].doofinder_hash_id == data[0].config.defaults.hashid) {
                                editDomainSelect.value = result[i].domain_id;
                            }
                        }
                    }
                });
            }.bind(this, id));
        },
        onCloseEditStoreModal() {
            this.showEditModal = false;
        },
        onSubmitEditStoreModal(id)
        {
            let loadingGif = document.getElementsByClassName('loadingGif')[0];
            loadingGif.style.display = 'block';

            let title = document.querySelector("[id*='intediaDoofinder_title']").value;
            let trigger = document.querySelector("[id*='intediaDoofinder_trigger']").value;
            let domainId = document.querySelector("[id*='intediaDoofinder_domainId_" + id + "']").value;

            if (domainId == '') {
                this.createNotificationError({
                    title: this.$tc('intedia-doofinder-stores.notification.error.title'),
                    message: this.$tc('intedia-doofinder-stores.notification.error.emptyDomainId')
                });
                loadingGif.style.display = 'none';
            } else {
                this.communicationService.editStore(id, domainId, title, trigger)
                    .then((result) => {
                        if (result.error) {
                            this.createNotificationError({
                                title: this.$tc('intedia-doofinder-stores.notification.error.title'),
                                message: this.$tc('intedia-doofinder-stores.notification.error.' + result.error)
                            });
                        } else {
                            this.communicationService.getStores().then((result) => {
                                this.createNotificationSuccess({
                                    title: this.$tc('intedia-doofinder-stores.notification.success.title'),
                                    message: this.$tc('intedia-doofinder-stores.notification.success.editStore')
                                });
                                this.dataSource = result;
                                this.showEditModal = false;
                            })
                        }
                        loadingGif.style.display = 'none';
                    });
            }
        },

        viewStore(doofinder_store_id) {
            window.open('https://admin.doofinder.com/admin/store/settings?store_id=' +
                doofinder_store_id, '_blank');
        },
    }
});