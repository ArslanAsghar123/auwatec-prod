import template from './swpa-backup-settings.html.twig';
import './swpa-backup-settings.scss';

const {Mixin} = Shopware;

Shopware.Component.register('swpa-backup-settings', {
    template,

    mixins: [
        Mixin.getByName('notification')
    ],

    inject: [
        'acl',
        'SwpaBackupConnectionTestSftpService',
        'SwpaBackupConnectionTestFtpService',
        'SwpaBackupConnectionTestAwsService',
        'SwpaBackupConnectionTestLocalService',
        'SwpaBackupService'
    ],

    data() {
        return {
            isLoading: false,
            config: null,
            savingDisabled: false,
            isSaveSuccessful: false,
            isCreateBackupInProgress: false,
            isValid: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        canCreateBackup() {
            if (!this.hasAccess('editor')) {
                return false;
            }
            if (this.config && this.isValid) {
                if (this.config['SwpaBackup.settings.generalBackupEnable'] === false) {
                    return false;
                }
                return true;
            }
            return false;
        },

        isAvailable() {
            return this.hasAccess('editor');
        }
    },

    watch: {
        config: function (config) {
            this.config = config;
            if (this.config['SwpaBackup.settings.generalBackupEnable'] === true) {
                this.isValid = true;
            }
        }
    },

    mounted: function () {
        if (!this.hasAccess('editor')) {
            this.noAccessMessage();
        }
    },

    methods: {

        hasAccess(key) {
            if (this.acl === undefined) { // disabled
                return true;
            }
            return this.acl.can('swpa_backup.' + key);
        },

        onCreateBackup() {
            this.isCreateBackupInProgress = true;
            this.SwpaBackupService.create().then(response => {
                if (response.result === true) {
                    this.createNotificationSuccess({
                        title: this.$tc('swpa-backup.backup.messages.createSuccess.title'),
                        message: this.$tc('swpa-backup.backup.messages.createSuccess.text'),
                    });
                } else {
                    const defaultErrorMessage = this.$tc('swpa-backup.backup.messages.createFailed.text');
                    var errorMessage = '';
                    if (response.message.length > 0) {
                        response.message.forEach((message) => {
                            errorMessage += message + '<br/>';
                        });
                    }
                    this.createNotificationError({
                        title: this.$tc('swpa-backup.backup.messages.createFailed.title'),
                        message: response.message.length > 0 ? errorMessage : defaultErrorMessage,
                    });
                }
                this.isCreateBackupInProgress = false;
            }).catch((e) => {
                const errors = e.response.data.errors;
                const defaultErrorMessage = this.$tc('swpa-backup.backup.messages.createFailed.text');
                var errorMessage = '';
                if (errors.length > 0) {
                    errors.forEach((error) => {
                        errorMessage += error.detail + '<br/>';
                    })
                }
                this.createNotificationError({
                    title: this.$tc('swpa-backup.backup.messages.createFailed.title'),
                    message: errors.length > 0 ? errorMessage : defaultErrorMessage,
                });
                this.isCreateBackupInProgress = false;
            });
        },

        noAccessMessage() {
            this.createSystemNotificationWarning({
                title: this.$tc('swpa-backup.backup.messages.noAccess.title'),
                message: this.$tc('swpa-backup.backup.messages.noAccess.text'),
            });
        },

        onSave() {
            this.isLoading = true;
            switch (this.config['SwpaBackup.settings.generalDestinationFilesystem']) {
                case 'local' :
                    this.test(this.SwpaBackupConnectionTestLocalService);
                    break;
                case 'sftp' :
                    this.test(this.SwpaBackupConnectionTestSftpService);
                    break;
                case 'ftp' :
                    this.test(this.SwpaBackupConnectionTestFtpService);
                    break;
                case 'aws' :
                    this.test(this.SwpaBackupConnectionTestAwsService);
                    break;
                default:
                    this.isLoading = false;
                    break;
            }
        },

        test(service) {
            service.validateCredentials(this.config).then((response) => {
                if (response.credentialsValid === true) {
                    this.onValidateSuccess();
                } else if (response.credentialsValid === false) {
                    this.onValidatePermissionFailed();
                } else if (response.credentialsValid === null) {
                    if (response.message !== '') {
                        this.createNotificationError({
                            title: this.$tc('swpa-backup.connection.messages.error.title'),
                            message: response.message,
                        });
                    }
                    this.onValidateFailed();
                }
            }).catch(() => {
                this.onValidateFailed();
            });
        },

        onValidateSuccess() {
            this.createNotificationSuccess({
                title: this.$tc('swpa-backup.connection.messages.testConnectionSuccess.title'),
                message: this.$tc('swpa-backup.connection.messages.testConnectionSuccess.text'),
            });
            this.save();
            this.isValid = this.config['SwpaBackup.settings.generalBackupEnable'];
        },

        onValidateFailed() {
            this.isLoading = false;
            this.isValid = false;
            this.createNotificationError({
                title: this.$tc('swpa-backup.connection.messages.testConnectionFailed.title'),
                message: this.$tc('swpa-backup.connection.messages.testConnectionFailed.text'),
            });

            this.createSystemNotificationError({
                title: this.$tc('swpa-backup.settingForm.messages.savedError.title'),
                message: this.$tc('swpa-backup.settingForm.messages.savedError.text'),
            });
        },

        onValidatePermissionFailed() {
            this.isLoading = false;
            this.createNotificationError({
                title: this.$tc('swpa-backup.connection.messages.testPermissionFailed.title'),
                message: this.$tc('swpa-backup.connection.messages.testPermissionFailed.text'),
            });

            this.createSystemNotificationError({
                title: this.$tc('swpa-backup.settingForm.messages.savedError.title'),
                message: this.$tc('swpa-backup.settingForm.messages.savedError.text'),
            });
        },

        save() {
            this.$refs.configComponent.save().then((res) => {
                this.isLoading = false;
                this.isSaveSuccessful = true;
                if (res) {
                    this.config = res;
                }
                this.createSystemNotificationSuccess({
                    title: this.$tc('swpa-backup.settingForm.messages.savedSuccess.title'),
                    message: this.$tc('swpa-backup.settingForm.messages.savedSuccess.text'),
                });
            }).catch(() => {
                this.isSaveSuccessful = false;
                this.createSystemNotificationError({
                    title: this.$tc('swpa-backup.settingForm.messages.savedError.title'),
                    message: this.$tc('swpa-backup.settingForm.messages.savedError.text'),
                });
            }).finally(() => {
                this.isLoading = false;
            });
        },

    }
});
