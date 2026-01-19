describe('Check redirect creation', () => {
    beforeEach(() => {
        cy.loginViaApi()
            .then(() => {
                cy.visit('/admin#/dreisc/seo/redirect/create/base');
            });
    });

    /** We fix the internal url to category with this reset */
    it('Reset database', () => {
        cy.syncAndActivateE2eDatabase();
    });

    it('Internal URL to internal URL (301)', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Set sourceSalesChannelDomainId */
        cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form__field-sourceSalesChannelDomainId')
            .typeSingleSelect('http://www.shopware-dev.de', { exactMatch: 'http://www.shopware-dev.de' });

        /** Set sourcePath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-sourcePath').should('be.visible')
            .type('my/source/url/url-to-url');

        /** Set redirectSalesChannelDomainId */
        cy.get('.dreisc-seo-redirect-redirect-sales-channel-domain-form__field-redirectSalesChannelDomainId')
            .typeSingleSelect('http://www.shopware-dev.de/en', { exactMatch: 'http://www.shopware-dev.de/en' });

        /** Set redirectPath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectPath').should('be.visible')
            .type('my/redirect/url');

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();

        /** Check request */
        cy.wait('@redirectSaved').then(() => {
            /** Check if no loader is visible */
            cy.get('.sw-loader__element').should('not.exist');

            /**
             * Check the source url of the display field and start a request
             * */
            cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form a')
                .should('be.visible').and('have.attr', 'href')
                .should('include', 'http://www.shopware-dev.de/my/source/url/url-to-url')
                .then(href => {
                    cy.request({ url: href, failOnStatusCode: false })
                        .should((response) => {
                            expect(response).to.have.property('redirects');
                            expect('301: http://www.shopware-dev.de/en/my/redirect/url').to.eq(response.redirects[0]);
                        })
                });
        });
    });

    it('Internal URL to external URL (302)', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Set sourceSalesChannelDomainId */
        cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form__field-sourceSalesChannelDomainId')
            .typeSingleSelect('http://www.shopware-dev.de', { exactMatch: 'http://www.shopware-dev.de' });

        /** Set redirectHttpStatusCode */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectHttpStatusCode')
            .select('302');

        /** Set sourcePath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-sourcePath').should('be.visible')
            .type('my/source/url/url-to-external-url');

        /** Activate the redirect type "external url" */
        cy.get('.sw-tabs-item__redirectType-externalUrl').click();

        /** Make sure that the field redirectPath is not visible */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectPath').should('be.not.visible');

        /** Set redirectPath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectUrl').should('be.visible')
            .type('https://de.dreischild.com/');

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();

        /** Check request */
        cy.wait('@redirectSaved').then(() => {
            /** Check if no loader is visible */
            cy.get('.sw-loader__element').should('not.exist');

            /**
             * Check the source url of the display field and start a request
             * */
            cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form a')
                .should('be.visible').and('have.attr', 'href')
                .should('include', 'http://www.shopware-dev.de/my/source/url/url-to-external-url')
                .then(href => {
                    cy.request({ url: href, failOnStatusCode: false })
                        .should((response) => {
                            expect(response).to.have.property('redirects');
                            expect(response.redirects[0]).to.eq('302: https://de.dreischild.com/');
                            expect(response.body).to.contains('Dreischild GmbH - Shopware Agentur - Trier');
                        })
                });
        });
    });

    it('Internal URL to product', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Set sourceSalesChannelDomainId */
        cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form__field-sourceSalesChannelDomainId')
            .typeSingleSelect('http://www.shopware-dev.de', { exactMatch: 'http://www.shopware-dev.de' });

        /** Set redirectHttpStatusCode */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectHttpStatusCode')
            .select('301');

        /** Set sourcePath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-sourcePath').should('be.visible')
            .type('my/source/url/url-to-product');

        /** Activate the redirect type "product" */
        cy.get('.sw-tabs-item__redirectType-product').click();

        /** Set redirectProductId */
        cy.get('.dreisc-seo-redirect-detail-base__field-redirectProductId').should('be.visible')
            .typeSingleSelect('SW-1001', { selectContains: 'Standard Product 1001'});

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();

        /** Check request */
        cy.wait('@redirectSaved').then(() => {
            /** Check if no loader is visible */
            cy.get('.sw-loader__element').should('not.exist');

            /**
             * Check the source url of the display field and start a request
             * */
            cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form a')
                .should('be.visible').and('have.attr', 'href')
                .should('include', 'http://www.shopware-dev.de/my/source/url/url-to-product')
                .then(href => {
                    cy.request({ url: href, failOnStatusCode: false })
                        .should((response) => {
                            expect(response).to.have.property('redirects');
                            expect(response.redirects[0].substring(0, 3)).to.eq('301');
                            const redirectHref = response.redirects[0].substring(5);

                            /** Make sure we redirect to the same domain */
                            expect(redirectHref).to.contains('http://www.shopware-dev.de/');

                            /** Check the detail page */
                            cy.visit(redirectHref);
                            cy.get('h1').contains('Standard Produkt 1001')
                        })
                });
        });
    });

    it('Internal URL to product with deviating redirect sales channel domain', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Set sourceSalesChannelDomainId */
        cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form__field-sourceSalesChannelDomainId')
            .typeSingleSelect('http://www.shopware-dev.de', { exactMatch: 'http://www.shopware-dev.de' });

        /** Set redirectHttpStatusCode */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectHttpStatusCode')
            .select('301');

        /** Set sourcePath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-sourcePath').should('be.visible')
            .type('my/source/url/url-to-product-deviating');

        /** Activate the redirect type "product" */
        cy.get('.sw-tabs-item__redirectType-product').click();

        /** Set redirectProductId */
        cy.get('.dreisc-seo-redirect-detail-base__field-redirectProductId').should('be.visible')
            .typeSingleSelect('SW-1001', { selectContains: 'Standard Product 1001'});

        /** Activate hasDeviatingRedirectSalesChannelDomain */
        cy.get('#sw-field--dreiscSeoRedirectEntity-hasDeviatingRedirectSalesChannelDomain').should('be.visible')
            .click();

        /** Set deviatingRedirectSalesChannelDomainId */
        cy.get('.dreisc-seo-redirect-detail-base__field-deviatingRedirectSalesChannelDomainId').should('be.visible')
            .typeSingleSelect('http://gbshop.shopware-dev.de', { exactMatch: 'http://gbshop.shopware-dev.de' });

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();

        /** Check request */
        cy.wait('@redirectSaved').then(() => {
            /** Check if no loader is visible */
            cy.get('.sw-loader__element').should('not.exist');

            /**
             * Check the source url of the display field and start a request
             * */
            cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form a')
                .should('be.visible').and('have.attr', 'href')
                .should('include', 'http://www.shopware-dev.de/my/source/url/url-to-product-deviating')
                .then(href => {
                    cy.request({ url: href, failOnStatusCode: false })
                        .should((response) => {
                            expect(response).to.have.property('redirects');
                            expect(response.redirects[0].substring(0, 3)).to.eq('301');
                            const redirectHref = response.redirects[0].substring(5);

                            /** Make sure we redirect to the deviating domain */
                            expect(redirectHref).to.contains('http://gbshop.shopware-dev.de/');

                            /** Check the detail page */
                            cy.visit(redirectHref);
                            cy.get('h1').contains('Standard Product 1001');

                            /** Bugfix: We have to go back to the admin. Otherwise the next test will fail */
                            cy.visit('/admin#/dreisc/seo/redirect/create/base');
                        })
                });
        });
    });

    it('Internal URL to category', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Set sourceSalesChannelDomainId */
        cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form__field-sourceSalesChannelDomainId')
            .typeSingleSelect('http://www.shopware-dev.de', { exactMatch: 'http://www.shopware-dev.de' });

        /** Set redirectHttpStatusCode */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectHttpStatusCode')
            .select('301');

        /** Set sourcePath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-sourcePath').should('be.visible')
            .type('my/source/url/url-to-category');

        /** Activate the redirect type "category" */
        cy.get('.sw-tabs-item__redirectType-category').click();

        /** Set redirectCategoryId */
        cy.get('.dreisc-seo-redirect-detail-base__field-redirectCategoryId').should('be.visible')
            .typeSingleSelect('Standard-Products', { selectContains: 'Mainshop » Products' });

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();

        /** Check request */
        cy.wait('@redirectSaved').then(() => {
            /** Check if no loader is visible */
            cy.get('.sw-loader__element').should('not.exist');

            /**
             * Check the source url of the display field and start a request
             * */
            cy.get('.dreisc-seo-redirect-source-sales-channel-domain-form a')
                .should('be.visible').and('have.attr', 'href')
                .should('include', 'http://www.shopware-dev.de/my/source/url/url-to-category')
                .then(href => {
                    cy.request({ url: href, failOnStatusCode: false })
                        .should((response) => {
                            expect(response).to.have.property('redirects');
                            expect(response.redirects[0].substring(0, 3)).to.eq('301');
                            const redirectHref = response.redirects[0].substring(5);

                            /** Make sure we redirect to the same domain */
                            expect(redirectHref).to.contains('http://www.shopware-dev.de/');

                            /** Check the detail page */
                            cy.visit(redirectHref);
                            cy.get('div').contains('Beschreibung der Kategorie')
                        })
                });
        });
    });

    it('Product URL to product with edit after test', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Set redirectHttpStatusCode */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectHttpStatusCode')
            .select('301');

        /** Activate the source type "product" */
        cy.get('.sw-tabs-item__sourceType-product').click();

        /** Set sourceProductId */
        cy.get('.dreisc-seo-redirect-detail-base__field-sourceProductId').should('be.visible')
            .typeSingleSelect('SW-1000', { selectContains: 'Standard Product 1000'});

        /** Activate the redirect type "product" */
        cy.get('.sw-tabs-item__redirectType-product').click();

        /** Set redirectProductId */
        cy.get('.dreisc-seo-redirect-detail-base__field-redirectProductId').should('be.visible')
            .typeSingleSelect('SW-1001', { selectContains: 'Standard Product 1001'});

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();

        /** Check request */
        cy.wait('@redirectSaved').then(() => {
            /** Check if active field is disabled */
            cy.get('.dreisc-seo-redirect-detail-base__field-active').should('have.class', 'is--disabled');

            /** Edit again */
            cy.get('.dreisc-seo-redirect-detail__open-edit-mode-action').should('be.visible')
                .click();

            /** Set domain restriction */
            cy.get('#sw-field--dreiscSeoRedirectEntity-hasSourceSalesChannelDomainRestriction').should('be.visible')
                .click();

            /** Check if domain restriction is available */
            cy.get('.sw-promotion-basic-form__select-sales-channels').should('be.visible');
        });
    });
});
