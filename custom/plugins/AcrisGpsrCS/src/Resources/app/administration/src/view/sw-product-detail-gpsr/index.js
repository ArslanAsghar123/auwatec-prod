import template from './sw-product-detail-gpsr.html.twig';
const { mapGetters, mapState} = Shopware.Component.getComponentHelper();

Shopware.Component.register('sw-product-detail-gpsr', {
    template,
    inject: ['systemConfigApiService'],

    props: {
        parentProduct: {
            type: Object,
        },
        loading: {
            type: Boolean,
        },
        mediaFormVisible: {
            type: Boolean,
        }
    },

    computed: {

        ...mapState('swProductDetail', [
            'product',
            'parentProduct'
        ]),

        ...mapGetters('swProductDetail', [
            'isLoading'
        ]),
    },
    data() {
        return {
            isLoadingGpsr : true,
            isExpandable: true,
            showDownloadModal: false,
            displaySetting: ''
        };
    },

    created() {
        this.getDisplay();
    },
    metaInfo() {
        return {
            title: this.$createTitle(this.product.translated.name),
        };
    },
    methods: {
        toggleExpand() {
            this.isExpandable = !this.isExpandable;
        },
        onOpenDownloadModal() {
            this.showDownloadModal = true;
        },
        async getDisplay() {
            let config = await this.systemConfigApiService.getValues('AcrisGpsrCS.config')
            this.displaySetting = config['AcrisGpsrCS.config.gpsrDisplay'];

            if(this.displaySetting != 'folded') {
                this.isExpandable = false;
            }
            this.isLoadingGpsr = false;

        },
    }
});