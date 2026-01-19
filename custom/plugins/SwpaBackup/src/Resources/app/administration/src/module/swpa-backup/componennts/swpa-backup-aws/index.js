import template from './swpa-backup-aws.html.twig';

const {Component, Mixin} = Shopware;

Component.register('swpa-backup-aws', {
    template,

    name: 'SwpaBackupAws',

    mixins: [
        Mixin.getByName('notification'),
    ],

    inject: ['SwpaBackupConnectionTestAwsService'],

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
