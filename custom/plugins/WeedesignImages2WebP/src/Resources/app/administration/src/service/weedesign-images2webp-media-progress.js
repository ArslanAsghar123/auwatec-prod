const ApiService = Shopware.Classes.ApiService;

class WeedesignImages2WebPMediaProgressApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'weedesign/images2webp') {
        super(httpClient, loginService, apiEndpoint);
    }

    /**
     * Loads the webp data
     *
     * @param {String|null} salesChannelId
     *
     * @returns {Promise}
     */
    check(salesChannelId = null) {
        const apiRoute = `_action/${this.getApiBasePath()}/media/progress/check`;

        return this.httpClient.post(
            apiRoute,
            {
                salesChannelId,
            },
            {
                headers: this.getBasicHeaders(),
            },
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default WeedesignImages2WebPMediaProgressApiService;
