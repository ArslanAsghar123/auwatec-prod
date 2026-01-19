describe('Bulk basic tree', () => {
    beforeEach(() => {
        cy.loginViaApi();
    });

    it('Make basic tests of the category tree', () => {
        cy.log('» Open the category "Mainshop » Products » Standard-Products"');
        cy.visit('/admin#/dreisc/seo/bulk/product/index');

        cy.log('» Test if the language select is visible and has the value "English"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-languageId').should('be.visible')
            .find('.sw-entity-single-select__selection-text')
            .contains('English');

        cy.log('» Check if the "GB Shop" category is visible');
        cy.get('.tree-link.category-id-2c14595e6e974658a10610d9ddcd701e')
            .find('.sw-tree-item__label')
            .contains('GB Shop');

        cy.log('» Check the template info of the "GB Shop" category');
        cy.get('.tree-link.category-id-2c14595e6e974658a10610d9ddcd701e')
            .find('.template-information')
            .should('be.visible')
            .contains('(Kein Template definiert)');

        cy.log('» Open the childs of the "GB Shop" category');
        cy.get('.tree-link.category-id-2c14595e6e974658a10610d9ddcd701e')
            .parents('.sw-tree-item')
            .find('.sw-tree-item__toggle')
            .click();

        cy.log('» Check if the "Products" category is visible');
        cy.get('.tree-link.category-id-772290eef76d4577ad0c9bf27a04abd1')
            .find('.sw-tree-item__label')
            .contains('Products');

        cy.log('» Check the template info of the "Products" category');
        cy.get('.tree-link.category-id-772290eef76d4577ad0c9bf27a04abd1')
            .find('.template-information')
            .should('be.visible')
            .contains('(Kein Template definiert)');

        cy.log('» Open the childs of the "Products" category');
        cy.get('.tree-link.category-id-772290eef76d4577ad0c9bf27a04abd1')
            .parentsUntil('.sw-tree-item')
            .find('.sw-tree-item__toggle')
            .click();

        cy.log('» Load the details of the "Standard-Products" category');
        cy.get('.tree-link.category-id-2f6b400a0b6449c9baa50831710c8a94')
            .click();

        cy.log('» Check if the "No template defined" message comes');
        cy.get('.dreisc-seo-bulk-detail-base')
            .find('.sw-alert__title')
            .contains('Kein Template definiert');

        cy.log('» Select the seo option "Meta Beschreibung"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-seoOption')
            .typeSingleSelect(null, { exactMatch: 'Meta Beschreibung' });

        cy.log('» Check if the "Standard-Products" category is visible');
        cy.get('.tree-link.category-id-2f6b400a0b6449c9baa50831710c8a94')
            .should('be.visible');

        cy.log('» Select the seo option "SEO-URL"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-seoOption')
            .typeSingleSelect(null, { exactMatch: 'SEO-URL' });

        cy.log('» Check if the "Standard-Products" category is not visible');
        cy.get('.tree-link.category-id-2f6b400a0b6449c9baa50831710c8a94')
            .should('be.not.visible');

        cy.log('» Check if the notification is visible');
        cy.get('.tree-notice .sw-alert__message')
            .should('be.visible')
            .contains('Wählen Sie bitte noch den Verkaufskanal aus');

        cy.log('» Select the sales channel "Mainshop"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-salesChannelId')
            .typeSingleSelect(null, { selectContains: 'GB Shop' });

        cy.log('» Check if the notification is not visible');
        cy.get('.tree-notice .sw-alert__message')
            .should('be.not.visible');

        cy.log('» Check if the "Standard-Products" category is visible again');
        cy.get('.tree-link.category-id-2f6b400a0b6449c9baa50831710c8a94')
            .should('be.visible');

        /**
         * Direkt URL Tests
        */

        cy.log('» Check direct link: Meta Titel / English / Main-Products');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/metaTitle/2fbb5fe2e29a4d70aa5854ce7ce3e20b/_/e403cec98def4844affc68465632b15d/base');
        cy.reload(true);

        cy.log('» Check if the "Main-Products" category is visible again');
        cy.get('.tree-link.category-id-e403cec98def4844affc68465632b15d')
            .should('be.visible');

        cy.log('» Check if the seo option is "Meta Titel"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-seoOption')
            .find('.sw-single-select__selection-text')
            .contains('Meta Titel');

        cy.log('» Check if the language is "English"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-languageId')
            .find('.sw-entity-single-select__selection-text')
            .contains('English');

        cy.log('» Check direct link: SEO-URL / Deutsch / GB Shop / GB-Products');
        cy.visit('/admin#/dreisc/seo/bulk/product/detail/url/4b7ede4274654b169f133147f6fa1490/4a8f660ce5a44fc9bd18c73ad3727b3b/cce6cc404e7345c3a06adb6e0dca863d/base');
        cy.reload(true);

        cy.log('» Check if the "GB-Products" category is visible again');
        cy.get('.tree-link.category-id-cce6cc404e7345c3a06adb6e0dca863d')
            .should('be.visible');

        cy.log('» Check if the seo option is "SEO-URL"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-seoOption')
            .find('.sw-single-select__selection-text')
            .contains('SEO-URL');

        cy.log('» Check if the sales channel is "GB Shop"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-salesChannelId')
            .find('.sw-entity-single-select__selection-text')
            .contains('GB Shop (gbshop.shopware-dev.de)');

        cy.log('» Check if the language is "Deutsch"');
        cy.get('.dreisc-seo-bulk-detail-tree__field-languageId')
            .find('.sw-entity-single-select__selection-text')
            .contains('Deutsch');
    });
});
