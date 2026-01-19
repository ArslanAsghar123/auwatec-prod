const ApiService = Shopware.Classes.ApiService;

class SwpaBackupConnectionTestAwsService extends ApiService {

    constructor(httpClient, loginService, apiEndpoint = 'swpa-backup') {
        super(httpClient, loginService, apiEndpoint);
    }

    validateCredentials(config) {
        const params = {};
        const headers = this.getBasicHeaders();

        return this.httpClient
                .post(`_action/${this.getApiBasePath()}/validate-aws-credentials`,
                        {config},
                        {params, headers})
                .then((response) => {
                    return ApiService.handleResponse(response);
                });
    }
}

export default SwpaBackupConnectionTestAwsService;
