const ApiService = Shopware.Classes.ApiService;
const LoginService = Shopware.Classes.LoginService;

class CommunicationService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = '') {
        super(httpClient, loginService, apiEndpoint);
        this.httpClient = httpClient;
    }

    getData() {
        return this.httpClient
            .get('/getDoofinderChannels', {
                headers: this.getBasicHeaders()
            })
            .then(response => response.data)
    }

    createSearchEngine(domainId, storefrontChannelId, doofinderStoreId) {
        return this.httpClient
            .post('/createDoofinderSearchEngine',
                {'domainId': domainId, 'storefrontChannelId': storefrontChannelId, 'doofinderStoreId': doofinderStoreId},
                {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }

    linkData(domainId, storefrontChannelId, doofinderStoreId, hashId) {
        return this.httpClient
            .post('/linkDoofinderSearchEngine',
                {'domainId': domainId, 'storefrontChannelId': storefrontChannelId, 'doofinderStoreId': doofinderStoreId, 'hashId': hashId},
                {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }

    deleteData(domainId) {
        return this.httpClient
            .post('/deleteDoofinderSearchEngine',
                {'domainId': domainId},
                {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }

    processIndex(domainId, storefrontChannelId, doofinderHashId) {
        return this.httpClient
            .post('/processDoofinderSearchIndex',
            {
                'domainId': domainId,
                'storefrontChannelId': storefrontChannelId,
                'doofinderHashId': doofinderHashId
            }, {
                headers: this.getBasicHeaders()
            })
            .then(response => response.data);
    }

    getProcess(domainId, storefrontChannelId, doofinderHashId) {
        return this.httpClient
            .get('/getProcessDoofinderSearchIndex/' +
                '?domainId=' + domainId +
                '&storefrontChannelId=' + storefrontChannelId +
                '&doofinderHashId=' + doofinderHashId, {
                headers: this.getBasicHeaders()
            })
            .then(response => response.data);
    }

    getStores() {
        return this.httpClient
            .get('/getStores', {
                headers: this.getBasicHeaders()
            })
            .then(response => response.data);
    }

    getStore(id) {
        return this.httpClient
            .get('/getStore/' +
                '?id=' + id, {
                headers: this.getBasicHeaders()
            })
            .then(response => response.data);
    }

    getLanguages() {
        return this.httpClient
            .get('/getLanguages/',
                {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }

    getCurrencies() {
        return this.httpClient
            .get('/getCurrencies/',
                {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }

    editStore(storeId, intediaDoofinder_storefrontId, intediaDoofinder_title, intediaDoofinder_trigger) {
        return this.httpClient
            .post('/editStore',
                {
                    'id': storeId,
                    'intediaDoofinder_domain_id': intediaDoofinder_storefrontId,
                    'intediaDoofinder_title': intediaDoofinder_title,
                    'intediaDoofinder_trigger': intediaDoofinder_trigger
                }, {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }

    createStore(intediaDoofinder_storefrontId, intediaDoofinder_title, intediaDoofinder_trigger) {
        return this.httpClient
            .post('/createStore',
                {
                    'intediaDoofinder_domain_id': intediaDoofinder_storefrontId,
                    'intediaDoofinder_title': intediaDoofinder_title,
                    'intediaDoofinder_trigger': intediaDoofinder_trigger
                }, {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }

    deleteStore(storeId)
    {
        return this.httpClient
            .post('/deleteStore',
                {
                    'id': storeId
                }, {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }

    deleteSearchEngineLink(id)
    {
        return this.httpClient
            .post('/deleteLink',
                {
                    'id': id
                }, {
                    headers: this.getBasicHeaders()
                })
            .then(response => response.data);
    }
}

export default CommunicationService;