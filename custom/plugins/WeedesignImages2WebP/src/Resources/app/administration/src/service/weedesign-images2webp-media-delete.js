const ApiService = Shopware.Classes.ApiService;

class WeedesignImages2WebPMediaDeleteApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'weedesign/images2webp') {
        super(httpClient, loginService, apiEndpoint);
    }

    /**
     * Removes all media files
     *
     * @param {String|null} salesChannelId
     *
     * @returns {Promise}
     */
    check(salesChannelId = null) {
        const apiRoute = `_action/${this.getApiBasePath()}/media/delete/run`;

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

export default WeedesignImages2WebPMediaDeleteApiService;
