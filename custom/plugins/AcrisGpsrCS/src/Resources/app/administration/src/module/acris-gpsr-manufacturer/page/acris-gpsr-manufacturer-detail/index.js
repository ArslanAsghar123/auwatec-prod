const {Component, Context, Utils, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './acris-gpsr-manufacturer-detail.html.twig';
import './acris-gpsr-manufacturer-detail.scss';

Component.register('acris-gpsr-manufacturer-detail', {
    template,

    inject: ['repositoryFactory', 'context', 'acl', 'cmsService'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            showDownloadModal: false,
            item: null,
            isLoading: false,
            processSuccess: false,
            repository: null,
            isSaveSuccessful: false,
        };
    },

        computed: {
            gpsrManufacturerCriteria() {
                const criteria = new Criteria(this.page, this.limit);
                criteria.addAssociation("salesChannels");
                criteria.addAssociation("rules");
                criteria.addAssociation("productStreams");
                criteria.addAssociation("acrisGpsrManufacturerDownloads");

                return criteria
            },

            gpsrManufacturerRepository() {
                return this.repositoryFactory.create('acris_gpsr_mf');
            },

            displayTypeOptions() {
                return [
                    {label: this.$tc('acris-gpsr-manufacturer.detail.fieldTitleDisplayTypeOptionGpsrTab'), value: 'gpsrTab'},
                    {label: this.$tc('acris-gpsr-manufacturer.detail.fieldTitleDisplayTypeOptionTab'), value: 'tab'},
                    {label: this.$tc('acris-gpsr-manufacturer.detail.fieldTitleDisplayTypeOptionDescription'), value: 'description'}
                ]
            },

            tabPositionOptions() {
                return [
                    {label: this.$tc('acris-gpsr-manufacturer.detail.optionBeforeDescriptionTab'), value: 'beforeDescriptionTab'},
                    {label: this.$tc('acris-gpsr-manufacturer.detail.optionAfterDescriptionTab'), value: 'afterDescriptionTab'},
                    {label: this.$tc('acris-gpsr-manufacturer.detail.optionAfterReviewsTab'), value: 'afterReviewsTab'}
                ]
            },

            descriptionDisplayOptions() {
                return [
                    {label: this.$tc('acris-gpsr-manufacturer.detail.optionDescriptionTypeModal'), value: 'modal'},
                    {label: this.$tc('acris-gpsr-manufacturer.detail.optionDescriptionTypeBeneath'), value: 'amongEachOther'}
                ]
            },

            descriptionPositionOptions() {
                return [
                    {label: this.$tc('acris-gpsr-manufacturer.detail.optionBeforeDescriptionPosition'), value: 'beforeDescription'},
                    {label: this.$tc('acris-gpsr-manufacturer.detail.optionAfterDescriptionPosition'), value: 'afterDescription'}
                ]
            },

            separatorOptions() {
                return [
                    {label: this.$tc('acris-gpsr-manufacturer.detail.fieldTitleSeparatorOptionShow'), value: 'show'},
                    {label: this.$tc('acris-gpsr-manufacturer.detail.fieldTitleSeparatorOptionHide'), value: 'hide'}
                ]
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
            this.repository = this.repositoryFactory.create('acris_gpsr_mf');
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
                .get(this.$route.params.id, Shopware.Context.api, this.gpsrManufacturerCriteria)
                .then((entity) => {
                    this.item = entity;
                });
        },

        onClickSave() {
            this.isLoading = true;
            const titleSaveError = this.$tc('acris-gpsr-manufacturer.detail.titleSaveError');
            const messageSaveError = this.$tc('acris-gpsr-manufacturer.detail.messageSaveError');
            const titleSaveSuccess = this.$tc('acris-gpsr-manufacturer.detail.titleSaveSuccess');
            const messageSaveSuccess = this.$tc('acris-gpsr-manufacturer.detail.messageSaveSuccess');

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
    }
});
