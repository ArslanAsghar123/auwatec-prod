const Application = Shopware.Application;
import CommunicationService from "../services/communication.service";
Application.addServiceProvider('communication', (container) => {
    const initContainer = Application.getContainer('init');
    return new CommunicationService(initContainer.httpClient, container.loginService);
});