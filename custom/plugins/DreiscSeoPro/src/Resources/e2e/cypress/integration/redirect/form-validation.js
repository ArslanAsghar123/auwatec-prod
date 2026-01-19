describe('Check redirect form validation', () => {
    beforeEach(() => {
        cy.loginViaApi()
            .then(() => {
                cy.visit('/admin#/dreisc/seo/redirect/create/base');
            });
    });

    it('Source path should not start with a slash', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Set sourcePath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-sourcePath').should('be.visible')
            .type('/source-starts-with-a-slash');

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();

        /** Check is the sourcePath was fixed */
        cy.wait(200).then(() => {
            cy.get('#sw-field--dreiscSeoRedirectEntity-sourcePath')
                .should('have.value', 'source-starts-with-a-slash')
        });
    });

    it('Redirect path should not start with a slash', () => {
        cy.server();
        cy.route({
            url: '/api/v1/search/dreisc-seo-redirect',
            method: 'post'
        }).as('redirectSaved');

        /** Set redirectPath */
        cy.get('#sw-field--dreiscSeoRedirectEntity-redirectPath').should('be.visible')
            .type('/source-starts-with-a-slash');

        /** Click save button */
        cy.get('button.dreisc-seo-redirect-detail__save-action').click();

        /** Check is the redirectPath was fixed */
        cy.wait(200).then(() => {
            cy.get('#sw-field--dreiscSeoRedirectEntity-redirectPath')
                .should('have.value', 'source-starts-with-a-slash')
        });
    });
});
