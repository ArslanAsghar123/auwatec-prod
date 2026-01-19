import template from './swpa-backup-general.html.twig';

const {Component, Mixin} = Shopware;

Component.register('swpa-backup-general', {
    template,

    name: 'SwpaBackupGeneral',

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
        return {
            test: 1
        };
    },

    computed: {
        frequencyOptions() {
            return [
                {
                    id: 'daily',
                    name: this.$tc('swpa-backup.settingForm.general.generalRunFrequency.options.daily')
                },
                {
                    id: 'weekly',
                    name: this.$tc('swpa-backup.settingForm.general.generalRunFrequency.options.weekly')
                },
                {
                    id: 'monthly',
                    name: this.$tc('swpa-backup.settingForm.general.generalRunFrequency.options.monthly')
                }
            ];
        },
        timeOptions() {
            var times = [];

            for (let i = 0; i < 24; i++) {
                times[i] = {
                    id: i + ':00',
                    name: i + ':00'
                };
            }

            return times;
        },
        typeOptions() {
            return [
                {
                    id: 0,
                    name: this.$tc('swpa-backup.settingForm.general.generalBackupType.options.database')
                },
                {
                    id: 1,
                    name: this.$tc('swpa-backup.settingForm.general.generalBackupType.options.databaseMedia')
                },
                {
                    id: 2,
                    name: this.$tc('swpa-backup.settingForm.general.generalBackupType.options.system')
                },
                {
                    id: 3,
                    name: this.$tc('swpa-backup.settingForm.general.generalBackupType.options.systemWithoutMedia')
                }
            ];
        },
        saveOptions() {
            return [
                {
                    id: 10,
                    name: this.$tc('swpa-backup.settingForm.general.generalCleanPeriod.options.week')
                },
                {
                    id: 1,
                    name: this.$tc('swpa-backup.settingForm.general.generalCleanPeriod.options.month')
                },
                {
                    id: 3,
                    name: this.$tc('swpa-backup.settingForm.general.generalCleanPeriod.options.threeMonth')
                },
                {
                    id: 6,
                    name: this.$tc('swpa-backup.settingForm.general.generalCleanPeriod.options.sixMonth')
                }
            ];
        },
        destinationFilesystemOptions() {
            return [
                {
                    id: 'local',
                    name: this.$tc('swpa-backup.settingForm.general.generalDestinationFilesystem.options.local')
                },
                {
                    id: 'ftp',
                    name: this.$tc('swpa-backup.settingForm.general.generalDestinationFilesystem.options.ftp')
                },
                {
                    id: 'sftp',
                    name: this.$tc('swpa-backup.settingForm.general.generalDestinationFilesystem.options.sftp')
                },
                {
                    id: 'aws',
                    name: this.$tc('swpa-backup.settingForm.general.generalDestinationFilesystem.options.aws')
                }
            ];
        }
    },

    methods: {
        checkInheritance(value) {
            return true;
        }
    }
});
