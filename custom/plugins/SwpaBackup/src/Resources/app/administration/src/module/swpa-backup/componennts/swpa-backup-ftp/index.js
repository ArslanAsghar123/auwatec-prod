import template from './swpa-backup-ftp.html.twig';

const {Component, Mixin} = Shopware;

Component.register('swpa-backup-ftp', {
    template,

    name: 'SwpaBackupFtp',

    mixins: [
        Mixin.getByName('notification'),
    ],

    inject: ['SwpaBackupConnectionTestFtpService'],

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
