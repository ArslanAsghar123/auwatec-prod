import WeedesignImages2WebPMediaDeleteApiService from '../service/weedesign-images2webp-media-delete';
import WeedesignImages2WebPMediaGenerateApiService from '../service/weedesign-images2webp-media-generate';
import WeedesignImages2WebPMediaProgressApiService from '../service/weedesign-images2webp-media-progress';
import WeedesignImages2WebPMediaUpgradeApiService from '../service/weedesign-images2webp-media-upgrade';
import WeedesignImages2WebPMediaSkipApiService from '../service/weedesign-images2webp-media-skip';

const { Application } = Shopware;

const initContainer = Application.getContainer('init');

Application.addServiceProvider(
    'WeedesignImages2WebPMediaDeleteApiService',
    (container) => new WeedesignImages2WebPMediaDeleteApiService(initContainer.httpClient, container.loginService),
);

Application.addServiceProvider(
    'WeedesignImages2WebPMediaGenerateApiService',
    (container) => new WeedesignImages2WebPMediaGenerateApiService(initContainer.httpClient, container.loginService),
);

Application.addServiceProvider(
    'WeedesignImages2WebPMediaProgressApiService',
    (container) => new WeedesignImages2WebPMediaProgressApiService(initContainer.httpClient, container.loginService),
);

Application.addServiceProvider(
    'WeedesignImages2WebPMediaUpgradeApiService',
    (container) => new WeedesignImages2WebPMediaUpgradeApiService(initContainer.httpClient, container.loginService),
);

Application.addServiceProvider(
    'WeedesignImages2WebPMediaSkipApiService',
    (container) => new WeedesignImages2WebPMediaSkipApiService(initContainer.httpClient, container.loginService),
);