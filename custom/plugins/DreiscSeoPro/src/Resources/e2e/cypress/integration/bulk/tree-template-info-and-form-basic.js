describe('Tree template info and form basic', () => {
    beforeEach(() => {
        cy.loginViaApi();
    });

    /** Fix problems with previous tests */
    it('Reset database', () => {
        cy.syncAndActivateE2eDatabase();
    });

    it('Test tree template info and basic form actions', () => {
        cy.log('» Open the category "GB Shop » Products » Standard-Products"');
        cy.visit('/admin#/dreisc/seo/bulk/product/index');

        cy.log('» Load the details of the "GB-Shop" category');
        cy.get('.tree-link.category-id-2c14595e6e974658a10610d9ddcd701e')
            .click();

        cy.log('» Click the create button');
        cy.get('.dreisc-seo-bulk-detail__create-action')
            .should('be.visible')
            .click();

        cy.log('» Activate the "inherit template" option');
        cy.get('.dreisc-seo-bulk-detail-base__field-inherit')
            .click();

        cy.log('» Save the template settings');
        cy.get('.dreisc-seo-bulk-detail__save-action')
            .should('be.visible')
            .click();

        cy.log('» Check if the error is displayed, because the template is not set');
        cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId > div.sw-field__error')
            .should('be.visible');

        cy.log('» Select "Artikelname"');
        cy.get('.dreisc-seo-bulk-detail-base__field-dreiscSeoBulkTemplateId')
            .typeSingleSelect(null, { exactMatch: 'Artikelname' });

        cy.log('» Save the template settings');
        cy.get('.dreisc-seo-bulk-detail__save-action')
            .should('be.visible')
            .click();

        cy.log('» Check the template info of the "GB Shop" category');
        cy.get('.tree-link.category-id-2c14595e6e974658a10610d9ddcd701e')
            .find('.template-information')
            .should('be.visible')
            .contains('(Artikelname)');

        cy.log('» Open the childs of the "GB Shop" category');
        cy.get('.tree-link.category-id-2c14595e6e974658a10610d9ddcd701e')
            .parents('.sw-tree-item')
            .find('.sw-tree-item__toggle')
            .click();

        cy.log('» Check the template info of the "Products" category');
        cy.get('.tree-link.category-id-772290eef76d4577ad0c9bf27a04abd1')
            .find('.template-information')
            .should('be.visible')
            .contains('(vererbt von: GB Shop)');

        cy.log('» Open the childs of the "Products" category');
        cy.get('.tree-link.category-id-772290eef76d4577ad0c9bf27a04abd1')
            .parentsUntil('.sw-tree-item')
            .find('.sw-tree-item__toggle')
            .click();

        cy.log('» Check the template info of the "Standard-Products" category');
        cy.get('.tree-link.category-id-2f6b400a0b6449c9baa50831710c8a94')
            .find('.template-information')
            .should('be.visible')
            .contains('(vererbt von: GB Shop)');

        cy.log('» Click the delete button');
        cy.get('.dreisc-seo-bulk-detail__delete-action')
            .should('be.visible')
            .click();

        cy.log('» Click the confirm button');
        cy.get('.dreisc-seo-bulk-detail__confirm-button')
            .should('be.visible')
            .click();

        cy.log('» Check if the "No template defined" message comes');
        cy.get('.dreisc-seo-bulk-detail-base')
            .find('.sw-alert__title')
            .contains('Kein Template definiert');

        cy.log('» Check the template info of the "GB Shop" category');
        cy.get('.tree-link.category-id-2c14595e6e974658a10610d9ddcd701e')
            .find('.template-information')
            .should('be.visible')
            .contains('(Kein Template definiert)');
    });
});
