import template from './swpa-backup-local.html.twig';

const {Component, Mixin} = Shopware;

Component.register('swpa-backup-local', {
    template,

    name: 'SwpaBackupLocal',

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        actualConfigData: {
            type: Object,
            required: true
        },
        allConfigs: {
            type: Object,
            required: true
        },
        selectedSalesChannelId: {
            required: true
        },
        isLoading: {
            type: Boolean,
            required: true
        }
    },

    data() {
        return {};
    },

    computed: {},

    methods: {
        checkInheritance(value) {
            return true;
        }
    }
});
