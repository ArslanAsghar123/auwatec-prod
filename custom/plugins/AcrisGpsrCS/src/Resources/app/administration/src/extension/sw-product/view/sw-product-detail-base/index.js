import template from './sw-product-detail-base.html.twig';

const { Criteria } = Shopware.Data;
const { Component, Utils, Mixin } = Shopware;
const { isEmpty } = Utils.types;

Component.override('sw-product-detail-base', {
    template,
    inject: ['systemConfigApiService'],

    data() {
        return {
            showDownloadModal: false,
            acrisUploadTag: 'acrisProductDownloadsUploadTag',
            downloadDefaultFolderId: null,
            showGpsrUnderDetail: false,

        };
    },
    created() {
        this.getDisplay();


    },
    computed: {
        productDownloadRepository() {
            return this.repositoryFactory.create('acris_gprs_product_download');
        },
        productDownloadsEntityName() {
            return 'acris_gprs_product_download';
        },
    },
    methods: {
        getInheritValue(gpsrField) {
            if(!this.parentProduct || !this.parentProduct.hasOwnProperty("customFields")) return null;

            return this.parentProduct.customFields[gpsrField] ?? null;
        },
        async getDisplay() {
            let config = await this.systemConfigApiService.getValues('AcrisGpsrCS.config')

            if(config['AcrisGpsrCS.config.gpsrDisplay'] !== 'tab') {
                this.showGpsrUnderDetail = true;
            }
        },
        onOpenMedia() {
            this.$emit('media-open');
        },
        onOpenDownloadModal() {
            this.showDownloadModal = true;
        },
    }
});