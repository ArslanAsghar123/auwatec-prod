import template from './acris-gpsr-product-detail.html.twig';
const { mapGetters, mapState} = Shopware.Component.getComponentHelper();

const { Component, Mixin } = Shopware;


Component.register('acris-gpsr-product-detail', {
    template,
    props: {
        isLoadingGpsr: {
            type: Boolean,
        },
        gpsrTitle: {
            type: String,
        }
    },
    data() {
        return {
            showDownloadModal: false,
            mediaFormVisible: true,
        };
    },
    computed: {

        ...mapState('swProductDetail', [
            'product',
            'parentProduct'
        ]),
    },
    methods: {
        getInheritValue(gpsrField) {
            const p = this.parentProduct;
            if(!p) {
                return null;
            }

            if(!p['customFields']) {
                return null;
            }

            if(!p.customFields[gpsrField])
            {
                return null;
            }
            return p.customFields[gpsrField];
        },
        onOpenDownloadModal() {
            this.showDownloadModal = true;
        },
    }
});

