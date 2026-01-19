const {Component, Context, Utils, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './acris-gpsr-note-detail.html.twig';
import './acris-gpsr-note-detail.scss';

Component.register('acris-gpsr-note-detail', {
    template,

    inject: ['repositoryFactory', 'context', 'acl', 'cmsService'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    watch: {
        'item.media'() {
            if (this.item && this.item.media && this.item.media.id) {
                this.setMediaItem({targetId: this.item.media.id});
            }
        }
    },

    data() {
        return {
            showDownloadModal:false,
            item: null,
            isLoading: false,
            processSuccess: false,
            repository: null,
            mediaItem: null,
            uploadTag: 'acris-gpsr-upload-tag',
            isSaveSuccessful: false,
        };
    },

        computed: {
            gpsrNoteCriteria() {
                const criteria = new Criteria(this.page, this.limit);
                criteria.addAssociation("salesChannels");
                criteria.addAssociation("rules");
                criteria.addAssociation("productStreams");
                criteria.addAssociation("acrisGpsrNoteDownloads");

                return criteria
            },

            mediaRepository() {
                return this.repositoryFactory.create('media');
            },

            gpsrNoteRepository() {
                return this.repositoryFactory.create('acris_gpsr_note');
            },

            typeOptions() {
                return [
                    {label: this.$tc('acris-gpsr-note.detail.fieldSelectOptionTypeWaring'), value: 'warning'},
                    {label: this.$tc('acris-gpsr-note.detail.fieldSelectOptionTypeSafety'), value: 'safety'},
                    {label: this.$tc('acris-gpsr-note.detail.fieldSelectOptionTypeInfo'), value: 'information'}
                ]
            },

            displayTypeOptions() {
                return [
                    {label: this.$tc('acris-gpsr-note.detail.fieldTitleDisplayTypeOptionGpsrTab'), value: 'gpsrTab'},
                    {label: this.$tc('acris-gpsr-note.detail.fieldTitleDisplayTypeOptionTab'), value: 'tab'},
                    {label: this.$tc('acris-gpsr-note.detail.fieldTitleDisplayTypeOptionDescription'), value: 'description'}
                ]
            },

            tabPositionOptions() {
                return [
                    {label: this.$tc('acris-gpsr-note.detail.optionBeforeDescriptionTab'), value: 'beforeDescriptionTab'},
                    {label: this.$tc('acris-gpsr-note.detail.optionAfterDescriptionTab'), value: 'afterDescriptionTab'},
                    {label: this.$tc('acris-gpsr-note.detail.optionAfterReviewsTab'), value: 'afterReviewsTab'}
                ]
            },

            descriptionDisplayOptions() {
                return [
                    {label: this.$tc('acris-gpsr-note.detail.optionDescriptionTypeModal'), value: 'modal'},
                    {label: this.$tc('acris-gpsr-note.detail.optionDescriptionTypeBeneath'), value: 'amongEachOther'}
                ]
            },

            descriptionPositionOptions() {
                return [
                    {label: this.$tc('acris-gpsr-note.detail.optionBeforeDescriptionPosition'), value: 'beforeDescription'},
                    {label: this.$tc('acris-gpsr-note.detail.optionAfterDescriptionPosition'), value: 'afterDescription'}
                ]
            },

            separatorOptions() {
                return [
                    {label: this.$tc('acris-gpsr-note.detail.fieldTitleSeparatorOptionShow'), value: 'show'},
                    {label: this.$tc('acris-gpsr-note.detail.fieldTitleSeparatorOptionHide'), value: 'hide'}
                ]
            },

            mediaDefaultFolderRepository() {
                return this.repositoryFactory.create('media_default_folder');
            },
        },

    created() {
        this.createdComponent();
    },

    methods: {
        onOpenDownloadModal() {
            this.showDownloadModal = true;
        },

        onCloseDownloadModal() {
            this.showDownloadModal = false;
        },
        createdComponent() {
            this.repository = this.repositoryFactory.create('acris_gpsr_note');
            this.getEntity();
        },

        onCancel() {
            this.$emit('modal-close');
        },

        onApply() {
            this.$emit('modal-save', this.documentItem);
        },


        getEntity() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.gpsrNoteCriteria)
                .then((entity) => {
                    this.item = entity;
                });
        },

        onClickSave() {
            this.isLoading = true;
            const titleSaveError = this.$tc('acris-gpsr-note.detail.titleSaveError');
            const messageSaveError = this.$tc('acris-gpsr-note.detail.messageSaveError');
            const titleSaveSuccess = this.$tc('acris-gpsr-note.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('acris-gpsr-note.detail.messageSaveSuccess');

            this.isSaveSuccessful = false;
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getEntity();
                    this.isLoading = false;
                    this.processSuccess = true;
                    this.createNotificationSuccess({
                        title: titleSaveSuccess,
                        message: messageSaveSuccess
                    });
                }).catch(() => {
                this.isLoading = false;
                this.createNotificationError({
                    title: titleSaveError,
                    message: messageSaveError
                });
            });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        onChangeLanguage() {
            this.getEntity();
        },

        onSalesChannelChange(newSalesChannels) {
            this.item.salesChannels = newSalesChannels;
            this.$emit('update:collection', newSalesChannels);
        },
        onProductStreamChange(newProductStreams){
            this.item.productStreams = newProductStreams;
            this.$emit('update:collection', newProductStreams);
        },
        onRulesChange(newRules){
            this.item.rules = newRules;
            this.$emit('update:collection', newRules);
        },

        setMediaItem({ targetId }) {
            this.item.mediaId = targetId;
        },

        onDropMedia(mediaItem) {
            this.setMediaItem({targetId: mediaItem.id});
        },

        setMediaFromSidebar(mediaEntity) {
            this.item.mediaId = mediaEntity.id;
        },

        onUnlinkAvatar() {
            this.item.mediaId = null;
        },

        openMediaSidebar() {
            this.$refs.mediaSidebarItem.openContent();
        },
    }
});
