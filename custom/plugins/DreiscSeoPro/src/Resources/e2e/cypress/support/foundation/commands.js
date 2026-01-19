/**
 * THIS FILE IS WRITE PROTECTED!
 */

/**
 * Switches administration UI locale to EN_GB
 * @memberOf Cypress.Chainable#
 * @name setLocaleToEnGb
 * @function
 */
Cypress.Commands.add('setLocaleToEnGb', () => {
    return cy.window().then((win) => {
        win.localStorage.setItem('sw-admin-locale', Cypress.env('locale'));
});
});

/**
 * Cleans up any previous state by restoring database and clearing caches
 * @memberOf Cypress.Chainable#
 * @name openInitialPage
 * @function
 */
Cypress.Commands.add('openInitialPage', (url) => {
    // Request we want to wait for later
    cy.server();
cy.route('/api/v1/_info/me').as('meCall');


cy.visit(url);
cy.wait('@meCall').then(() => {
    cy.get('.sw-desktop').should('be.visible');
});
});

/**
 * Authenticate towards the Shopware API
 * @memberOf Cypress.Chainable#
 * @name authenticate
 * @function
 */
Cypress.Commands.add('authenticate', () => {
    return cy.request(
        'POST',
        '/api/oauth/token',
        {
            grant_type: Cypress.env('grant') ? Cypress.env('grant') : 'password',
            client_id: Cypress.env('client_id') ? Cypress.env('client_id') : 'administration',
            scopes: Cypress.env('scope') ? Cypress.env('scope') : 'write',
            username: Cypress.env('user') ? Cypress.env('user') : 'admin',
            password: Cypress.env('pass') ? Cypress.env('pass') : 'shopware'
        }
    ).then((responseData) => {
        return {
            access: responseData.body.access_token,
            refresh: responseData.body.refresh_token,
            expiry: Math.round(+new Date() / 1000) + responseData.body.expires_in
        };
});
});

/**
 * Logs in silently using Shopware API
 * @memberOf Cypress.Chainable#
 * @name loginViaApi
 * @function
 */
Cypress.Commands.add('loginViaApi', () => {
    return cy.authenticate().then((result) => {
        return cy.window().then((win) => {
            win.localStorage.setItem('bearerAuth', JSON.stringify(result));
            // Return bearer token
            return win.localStorage.getItem('bearerAuth');
        }, [result], (data) => {
            if (!data.value) {
                cy.login('admin');
            }
        });
    });
});

/**
 * Click context menu in order to cause a desired action
 * @memberOf Cypress.Chainable#
 * @name clickContextMenuItem
 * @function
 * @param {String} menuButtonSelector - The message to look for
 * @param {String} menuOpenSelector - The message to look for
 * @param {Object} [scope=null] - Options concerning the notification
 */
Cypress.Commands.add('clickContextMenuItem', (menuButtonSelector, menuOpenSelector, scope = null) => {
    const contextMenuCssSelector = '.sw-context-menu';
const activeContextButtonCssSelector = '.is--active';

if (scope != null) {
    cy.get(scope).should('be.visible');
    cy.get(`${scope} ${menuOpenSelector}`).click({ force: true });

    if (scope.includes('row')) {
        cy.get(`${menuOpenSelector}${activeContextButtonCssSelector}`).should('be.visible');
    }
} else {
    cy.get(menuOpenSelector).should('be.visible').click({ force: true });
}

cy.get(contextMenuCssSelector).should('be.visible');
cy.get(menuButtonSelector).click();
cy.get(contextMenuCssSelector).should('not.exist');
});

/**
 * @memberOf Cypress.Chainable#
 * @name syncAndActivateE2eDatabase
 * @function
 */
Cypress.Commands.add('syncAndActivateE2eDatabase', () => {
    return cy.log('Sync "c1shopwareDev_e2e" database with "c1shopwareDev" PROD database.').then(() => {
        return cy.exec(`${Cypress.env('shopwareRootDir')}/bin/console _:system:test-database-reset c1shopwareDev_e2e`).then(() => {
            return cy.log('Set database to "c1shopwareDev_e2e" (.env file)).').then(() => {
                return cy.exec(`${Cypress.env('shopwareRootDir')}/bin/console _:system:set-env-database c1shopwareDev_e2e`).then(() => {
                    return cy.log('Reset the demodata').then(() => {
                        return cy.exec(`${Cypress.env('shopwareRootDir')}/bin/console _demo`)
                    });
                });
            });
        });
    });
});

/**
 * @memberOf Cypress.Chainable#
 * @name resetEnvDatabase
 * @function
 */
Cypress.Commands.add('resetEnvDatabase', () => {
    return cy.log('Reset database to "shopware" (.env file)).').then(() => {
        return cy.exec(`${Cypress.env('shopwareRootDir')}/bin/console _:system:set-env-database c1shopwareDev`)
    });
});

/**
 * Types in an swSelect field
 * @memberOf Cypress.Chainable#
 * @name typeSingleSelect
 * @function
 * @param {String} value - Desired value of the element
 * @param {Object} [options={}] - Options concerning swSelect usage
 */
Cypress.Commands.add('typeSingleSelect', {
    prevSubject: 'element'
    }, (subject, value, options = {}) => {
    const resultPrefix = '.sw-single-select';
    const inputCssSelector = '.sw-entity-single-select__selection-input';
    const searchTerm = options.searchTerm || value;
    const selectContains = options.selectContains || null;
    const exactMatch = options.exactMatch || null;
    const position = options.position || 0;

    cy.wrap(subject).should('be.visible');
    cy.wrap(subject).click();

        cy.get('.sw-select-result-list').should('be.visible');

        if (null !== searchTerm) {
            cy.get(`${subject.selector} ${inputCssSelector}`).clear();
            cy.get(`${subject.selector} ${inputCssSelector}`).type(searchTerm);
            cy.get(`${subject.selector} ${inputCssSelector}`).should('have.value', searchTerm);
            cy.wait(200);
        }

        cy.wait(1000).then(() => {
                if(null !== exactMatch) {
                    let exactMatchFound = false;
                    cy.get(`${subject.selector} .sw-select-result__result-item-text`).each(ele => {
                        if (ele.text() === exactMatch) {
                            ele.click();
                            exactMatchFound = true;
                        }

                        if (false === exactMatchFound) {
                            cy.log('No exactMatch found');
                        }
                    });
                } else {
                    cy.get(`${subject.selector} .sw-select-result-list`)
                        .contains(null !== selectContains ? selectContains : searchTerm)
                        .click();
                }

        });

    return this;
});
