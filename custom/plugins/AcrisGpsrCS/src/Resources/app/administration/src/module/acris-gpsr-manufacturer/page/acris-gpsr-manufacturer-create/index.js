const {Component} = Shopware;
const utils = Shopware.Utils;

Component.extend('acris-gpsr-manufacturer-create', 'acris-gpsr-manufacturer-detail', {
    beforeRouteEnter(to, from, next) {
        if (to.name.includes('acris.gpsr.manufacturer.create') && !to.params.id) {
            to.params.id = utils.createId();
            to.params.newItem = true;
        }

        next();
    },

    created() {
        this.createdComponent();
    },

    methods: {
        getEntity() {
            this.item = this.repository.create(Shopware.Context.api);
            this.item.active = true;
            this.item.priority = 10;
            this.item.displayType = 'description';
            this.item.tabPosition = 'afterReviewsTab';
            this.item.descriptionDisplay = 'amongEachOther';
            this.item.descriptionPosition = 'afterDescription';
            this.item.displaySeparator = 'show';
        },

        createdComponent() {
            if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                Shopware.State.commit('context/resetLanguageToDefault');
            }

            this.$super('createdComponent');
        },

        saveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({name: 'acris.gpsr.manufacturer.detail', params: {id: this.item.id}});
        },

        onClickSave() {
            this.isLoading = true;
            const titleSaveError = this.$tc('acris-gpsr-manufacturer.detail.titleSaveError');
            const messageSaveError = this.$tc('acris-gpsr-manufacturer.detail.messageSaveError');
            const titleSaveSuccess = this.$tc('acris-gpsr-manufacturer.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('acris-gpsr-manufacturer.detail.messageSaveSuccess');

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.createNotificationSuccess({
                        title: titleSaveSuccess,
                        message: messageSaveSuccess
                    });
                    this.$router.push({name: 'acris.gpsr.manufacturer.detail', params: {id: this.item.id}});
                }).catch(() => {
                this.isLoading = false;
                this.createNotificationError({
                    title: titleSaveError,
                    message: messageSaveError
                });
            });
        }
    }
});
