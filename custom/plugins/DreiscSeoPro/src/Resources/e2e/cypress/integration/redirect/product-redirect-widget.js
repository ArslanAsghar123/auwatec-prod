describe('Check product redirect widget', () => {
    beforeEach(() => {
        cy.loginViaApi();
    });

    it('Reset database', () => {
        cy.syncAndActivateE2eDatabase();
    });

    it('Make sure that no redirect card is visible', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Open the product SW-1000 */
        cy.visit('/admin#/sw/product/detail/b7d2554b0ce847cd82f3ac9bd1c0dfca/base');

        cy.wait('@redirectSaved').then(() => {
            /** Make sure that the "no redirect" card is visible */
            cy.get('.no-redirect-text').scrollIntoView().should('be.visible');

            /** Follow the create link */
            cy.get('.no-redirect-text a').should('have.attr', 'href').then(href => {
                /** Open the create page */
                cy.visit('/admin' + href);

                /** Check if the source type product is active */
                cy.get('.sw-tabs-item__sourceType-product')
                    .should('have.class', 'sw-tabs-item--active');

                /** Check if the product is set */
                cy.get('.dreisc-seo-redirect-detail-base__field-sourceProductId')
                    .find('.sw-entity-single-select__selection-text')
                    .contains('Standard Product 1000');
            });
        });
    });

    it('Setup first redirect', () => {
        /** Set domain restriction */
        cy.get('#sw-field--dreiscSeoRedirectEntity-hasSourceSalesChannelDomainRestriction').should('be.visible')
            .click();

        /** Select the domains */
        cy.get('.sw-promotion-basic-form__select-sales-channels input').click();
        cy.get('.sw-select-result__result-item-text').contains('http://gbshop.shopware-dev.de').click();
        cy.get('.sw-promotion-basic-form__select-sales-channels input').click();
        cy.get('.sw-select-result__result-item-text').contains('http://www.shopware-dev.de').click();

        /** Activate the redirect type "product" */
        cy.get('.sw-tabs-item__redirectType-product').click();

        /** Set redirectProductId */
        cy.get('.dreisc-seo-redirect-detail-base__field-redirectProductId').should('be.visible')
            .typeSingleSelect('SW-1001', { selectContains: 'Standard Product 1001'});

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();
    });

    it('Make sure that redirect card is visible', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Open the product SW-1000 */
        cy.visit('/admin#/sw/product/detail/b7d2554b0ce847cd82f3ac9bd1c0dfca/base');

        cy.wait('@redirectSaved').then(() => {
            /** Make sure that the "no redirect" card is visible */
            cy.get('.redirect-info .redirect-text').should('be.visible');
            cy.get('.redirect-info .redirect-text .detail-toggle')
                .should('be.visible').click();

            /** Make that the redirect definition is correct */
            cy.get('.redirect-headline').should('have.length', 1);
            cy.get('.redirect-headline').contains('Weiterleitung auf ein Produkt');
            cy.get('.domain-list > li').should('have.length', 2);
            cy.get('.domain-list').contains('http://www.shopware-dev.de');
            cy.get('.domain-list').contains('http://gbshop.shopware-dev.de');
        });
    });

    it('Setup second redirect', () => {
        cy.visit('/admin#/dreisc/seo/redirect/create/base');

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

        /** Activate the redirect type "category" */
        cy.get('.sw-tabs-item__redirectType-category').click();

        /** Set redirectCategoryId */
        cy.get('.dreisc-seo-redirect-detail-base__field-redirectCategoryId').should('be.visible')
            .typeSingleSelect('Standard-Products', { selectContains: 'Mainshop » Products' });

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();
    });

    it('Check again tzhe redirect card', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Open the product SW-1000 */
        cy.visit('/admin#/sw/product/detail/b7d2554b0ce847cd82f3ac9bd1c0dfca/base');

        cy.wait('@redirectSaved').then(() => {
            /** Make sure that the "no redirect" card is visible */
            cy.get('.redirect-info .redirect-text').should('be.visible');
            cy.get('.redirect-info .redirect-text .detail-toggle')
                .should('be.visible').click();

            /** Make that the redirect definition is correct */
            cy.get('.redirect-headline').should('have.length', 2);
            cy.get('.redirect-headline').contains('Weiterleitung auf ein Produkt');
            cy.get('.redirect-headline').contains('Weiterleitung auf eine Kategorie');
            cy.get('.detail-description').contains('Die Weiterleitung wird für alle Domains durchgeführt.');
        });
    });
});
