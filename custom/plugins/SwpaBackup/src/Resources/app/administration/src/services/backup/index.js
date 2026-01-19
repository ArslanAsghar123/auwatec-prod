const ApiService = Shopware.Classes.ApiService;

class SwpaBackupService extends ApiService {

    constructor(httpClient, loginService, apiEndpoint = 'swpa-backup') {
        super(httpClient, loginService, apiEndpoint);
    }

    create() {
        const params = {};
        const headers = this.getBasicHeaders();
        return this.httpClient
                .post(`_action/${this.getApiBasePath()}/create`, {}, {params, headers})
                .then((response) => {
                    return ApiService.handleResponse(response);
                });
    }
}

export default SwpaBackupService;
