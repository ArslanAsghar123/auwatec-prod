import SwpaBackupConnectionTestSftpService from './connection/test/sftp';
import SwpaBackupConnectionTestFtpService from './connection/test/ftp';
import SwpaBackupConnectionTestAwsService from './connection/test/aws';
import SwpaBackupConnectionTestLocalService from './connection/test/local';

const {Application} = Shopware;

Application.addServiceProvider('SwpaBackupConnectionTestLocalService', (container) => {
    const initContainer = Application.getContainer('init');

    return new SwpaBackupConnectionTestLocalService(initContainer.httpClient, container.loginService);
});

Application.addServiceProvider('SwpaBackupConnectionTestFtpService', (container) => {
    const initContainer = Application.getContainer('init');

    return new SwpaBackupConnectionTestFtpService(initContainer.httpClient, container.loginService);
});

Application.addServiceProvider('SwpaBackupConnectionTestSftpService', (container) => {
    const initContainer = Application.getContainer('init');

    return new SwpaBackupConnectionTestSftpService(initContainer.httpClient, container.loginService);
});

Application.addServiceProvider('SwpaBackupConnectionTestAwsService', (container) => {
    const initContainer = Application.getContainer('init');

    return new SwpaBackupConnectionTestAwsService(initContainer.httpClient, container.loginService);
});
