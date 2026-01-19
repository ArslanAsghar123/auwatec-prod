const { Application } = Shopware;
const { Criteria } = Shopware.Data;

if (Shopware.coolbax === undefined) {
    Shopware.coolbax = {};
}

const pluginName = 'CbaxModulLexicon';

const container = Shopware.Application.getContainer();

const repositoryFactory = container.service.repositoryFactory;
const systemConfigApiService = container.service.systemConfigApiService;

let systemConfigRepository = repositoryFactory.create('system_config');

const criteria = new Criteria(1, 1);

criteria.addFilter(Criteria.contains('configurationKey', pluginName));
criteria.addFilter(Criteria.contains('configurationKey', 'updateWithNotification'));

systemConfigRepository.search(criteria, Shopware.Context.api).then((response) => {
    if (response.total > 0) {
        let applicationRoot = null;
        let loop = 1;
        let interval = setInterval(() => {
            applicationRoot = getApplicationRootReference(null);
            if (applicationRoot !== false) {
                // applicationRoot sollte promise sein
                applicationRoot.then(applicationRootObj => {
                    if (applicationRootObj !== false) {
                        clearInterval(interval);

                        const data = response.first();
                        const saveValues = {};

                        saveValues[data.configurationKey] = null;

                        createUpdateNotification(pluginName, data.configurationValue, applicationRootObj);

                        systemConfigApiService.saveValues(saveValues);
                    }
                })
            }

            loop++;

            // sollte nie erreicht werden, aber um eine theoretische Unendlich-Schleife zu verhindern...
            if (loop === 45) {
                clearInterval(interval);
            }
        }, 100);
    }
}).catch((err) => {
    // not needed
});

function createUpdateNotification(plugin, config, applicationRoot) {
    const lowerCasePlugin = plugin.toLowerCase();
    let title = lowerCasePlugin + '.updateNotification.default.title';
    let message = lowerCasePlugin + '.updateNotification.default.message';
    let action = {
        label: lowerCasePlugin + '.updateNotification.default.action',
        route: 'sw.extension.config',
        params: { namespace: plugin },
        multi: false
    };
    let extraParams = { variant: 'info',
        growl: true,
        system: true,
        autoClose: false
    };

    if (config.title !== undefined && config.title !== '') {
        title = config.title
    }

    if (config.message !== undefined && config.message !== '') {
        message = config.message
    }

    if (config.action !== undefined && config.action !== '') {
        action = config.action
    }

    if (config.extraParams !== undefined) {
        extraParams.variant = config.extraParams.variant;
        extraParams.growl = config.extraParams.growl;
        extraParams.system = config.extraParams.system;
        extraParams.autoClose = config.extraParams.autoClose;
    }

    let notification = {
        title: applicationRoot.$tc(
            title
        ),
        message: applicationRoot.$tc(
            message
        )
    };

    if (action.label !== undefined && (action.multi === undefined || action.multi === false) ) {
        if (action.params !== undefined && action.params !== false) {
            notification.actions = [{
                label: applicationRoot.$tc(
                    action.label
                ),
                route: { name: action.route, params: action.params }
            }];
        } else {
            notification.actions = [{
                label: applicationRoot.$tc(
                    action.label
                ),
                route: { name: action.route }
            }];
        }
    } else if (action.multi !== undefined && action.multi !== false) {
        action.multi.forEach((item, index) => {
            if (item.label !== undefined) {
                action.multi[index].label = applicationRoot.$tc(item.label);
            }
        });

        notification.actions = action.multi;
    }

    notification.variant = extraParams.variant;
    notification.growl = extraParams.growl;
    notification.system = extraParams.system;
    notification.autoClose = extraParams.autoClose;

    applicationRoot.config.globalProperties.$store.dispatch(
        'notification/createNotification',
        notification
    );
}

async function getApplicationRootReference(applicationRoot) {
    if (applicationRoot == null || applicationRoot === false) {
        applicationRoot = await Application.getApplicationRoot();
    }

    return applicationRoot;
}
