import SwpaBackupService from './backup/index';


const {Application} = Shopware;

Application.addServiceProvider('SwpaBackupService', (container) => {
    const initContainer = Application.getContainer('init');

    return new SwpaBackupService(initContainer.httpClient, container.loginService);
});
