describe('Check redirect list', () => {
    beforeEach(() => {
        cy.loginViaApi()
            .then(() => {
                cy.visit('/admin#/dreisc/seo/redirect/list');
            });
    });

    it('Headline is available', () => {
        // Open plugin configuration
        cy.get('.smart-bar__header').should('be.visible')
            .contains('301 und 302 URL Weiterleitungen');
    });

    it('Create button is visible', () => {
        // Open plugin configuration
        cy.get('.smart-bar__header').should('be.visible')
            .contains('301 und 302 URL Weiterleitungen');
    });
});
