import template from './swpa-backup-sftp.html.twig';

const {Component, Mixin} = Shopware;

Component.register('swpa-backup-sftp', {
    template,

    name: 'SwpaBackupSftp',

    mixins: [
        Mixin.getByName('notification'),
    ],

    inject: ['SwpaBackupConnectionTestSftpService'],

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
        checkTextFieldInheritance(value) {
            if (typeof value !== 'string') {
                return true;
            }

            return value.length <= 0;
        }
    }
});
