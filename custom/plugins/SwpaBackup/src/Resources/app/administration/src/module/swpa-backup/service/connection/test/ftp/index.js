const ApiService = Shopware.Classes.ApiService;

class SwpaBackupConnectionTestFtpService extends ApiService {

    constructor(httpClient, loginService, apiEndpoint = 'swpa-backup') {
        super(httpClient, loginService, apiEndpoint);
    }

    validateCredentials(config) {
        const params = {};
        const headers = this.getBasicHeaders();

        return this.httpClient
                .post(`_action/${this.getApiBasePath()}/validate-ftp-credentials`,
                        {config},
                        {params, headers})
                .then((response) => {
                    return ApiService.handleResponse(response);
                });
    }
}

export default SwpaBackupConnectionTestFtpService;
