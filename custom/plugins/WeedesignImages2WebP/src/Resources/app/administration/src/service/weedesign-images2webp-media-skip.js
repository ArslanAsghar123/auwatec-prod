const ApiService = Shopware.Classes.ApiService;

class WeedesignImages2WebPMediaSkipApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'weedesign/images2webp') {
        super(httpClient, loginService, apiEndpoint);
    }

    /**
     * Skip next file
     *
     * @param {String|null} salesChannelId
     *
     * @returns {Promise}
     */
    check(salesChannelId = null) {
        const apiRoute = `_action/${this.getApiBasePath()}/media/skip/run`;

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

export default WeedesignImages2WebPMediaSkipApiService;
