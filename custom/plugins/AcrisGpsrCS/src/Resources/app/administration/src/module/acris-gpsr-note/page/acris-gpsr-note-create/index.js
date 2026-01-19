const {Component} = Shopware;
const utils = Shopware.Utils;

Component.extend('acris-gpsr-note-create', 'acris-gpsr-note-detail', {
    beforeRouteEnter(to, from, next) {
        if (to.name.includes('acris.gpsr.note.create') && !to.params.id) {
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
            this.item.noteType = 'information';
            this.item.priority = 10;
            this.item.displayType = 'description';
            this.item.tabPosition = 'afterReviewsTab';
            this.item.descriptionDisplay = 'amongEachOther';
            this.item.descriptionPosition = 'afterDescription';
            this.item.displaySeparator = 'show';
            this.item.hintHeadlineSeoSize = 'h3';
            this.item.hintHeadlineSize = 'h4';
            this.item.hintAlignment = 'start';
            this.item.hintEnableHeadlineSize = false;
            this.item.mediaPosition = 'left';
            this.item.mediaSize = 100;
            this.item.imageUrlType = 'external';
            this.item.mobileVisibility = 'show';
        },

        createdComponent() {
            if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                Shopware.State.commit('context/resetLanguageToDefault');
            }

            this.$super('createdComponent');
        },

        saveFinish() {
            this.isSaveSuccessful = false;
            this.$router.push({name: 'acris.gpsr.note.detail', params: {id: this.item.id}});
        },

        onClickSave() {
            this.isLoading = true;
            const titleSaveError = this.$tc('acris-gpsr-note.detail.titleSaveError');
            const messageSaveError = this.$tc('acris-gpsr-note.detail.messageSaveError');
            const titleSaveSuccess = this.$tc('acris-gpsr-note.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('acris-gpsr-note.detail.messageSaveSuccess');

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.createNotificationSuccess({
                        title: titleSaveSuccess,
                        message: messageSaveSuccess
                    });
                    this.$router.push({name: 'acris.gpsr.note.detail', params: {id: this.item.id}});
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
