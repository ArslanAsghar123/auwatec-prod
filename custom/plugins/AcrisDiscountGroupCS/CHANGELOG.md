# 6.3.22
- Improves plugin compatibility.

# 6.3.21
- Optimization for time-limited discounts with "From" and "Until" dates in connection with the Shopware cache system.

# 6.3.20
- Improves plugin compatibility.

# 6.3.19
- Optimization for time-limited discounts in connection with the Shopware Cache System.

# 6.3.18
- Improved admin compatibility with Shopware 6.6.10.*

# 6.3.17
- Code optimizations.

# 6.3.16
- Improved compatibility of the admin view with Shopware 6.6.10.0.

# 6.3.15
- Improved compatibility with other plugins when they only load partial data from products.

# 6.3.14
- Improved compatibility with other plugins when they only load partial data from products.

# 6.3.13
- Improved compatibility with the ACRIS RRP plugin.

# 6.3.12
- Fixes potential issues with the calculation of discounts in connection with other plugins.

# 6.3.11
- Improved compatibility with other plugins when they only load partial data from products.

# 6.3.10
- Improved compatibility.

# 6.3.9
- Improves compatibility with the Shopware promotion.

# 6.3.8
- Optimizes displaying of the discounted price in the Storefront.

# 6.3.7
- Optimizes calculating of calculated prices.

# 6.3.6
- Improved testing of rounding configurations in the plugin.

# 6.3.5
- Refactored code added check if decimal places are changes, and if not to call parent class.

# 6.3.4
- Fixed cart total price when "Increased number of decimal places in the calculation, display configured as for the currency" is used.

# 6.3.3
- Fixed listing price bug.

# 6.3.2
- Fixed listing price bug.

# 6.3.1
- Fixed scale discount in the cart across products.

# 6.3.0
- Added config for "Number of decimal places for calculation and display in the cart".

# 6.2.0
- Added new configuration "Display of discount group and info text if there is no product of the corresponding discount group in the cart".
- Style optimisations of discount group show on detail page.

# 6.1.6
- Fixes a problem where products or customers could not be saved for directly assigned discount groups.

# 6.1.5
- Optimisation of rounding problems: From now on, the calculated price will be rounded for percentage discounts and not the discount itself.

# 6.1.4
- Fixes a possible problem where prices in the listing were calculated incorrectly.

# 6.1.3
- Improved caching and small performance optimisations.

# 6.1.2
- Removal of the rule condition "Article has cancellation price" from the dynamic product group because it did not work as desired.

# 6.1.1
- Removed "Item has list price" rule condition and added "List price" product dynamic group condition.

# 6.1.0
- Adds "Item has list price" rule condition.

# 6.0.11
- Improved compatibility with the ACRIS RRP plugin in the event of changes to graduated prices.

# 6.0.10
- Correction so that only import / export profiles inserted by the plugin itself are updated.

# 6.0.9
- Correction of the import / export profile inserted by the plugin.

# 6.0.8
- Fixed a compatibility issue with other plugins.
- Increased compatibility about loading performance by not loading not needed data.

# 6.0.7
- Fixed a bug with the calculated cheapest price in the product listing.

# 6.0.6
- Fixed a bug where a 500 Error was returned when no advanced pricing was set on the product.

# 6.0.5
- Improved compatibility with the ACRIS Order Matrix plugin. 

# 6.0.4
- Improved compatibility with the ACRIS UVP plugin.

# 6.0.3
- Improved compatibility with app scripts in the shopping cart.

# 6.0.2
- Fixed calculating price error.

# 6.0.1
- Optimized compiled js files.

# 6.0.0
- Compatibility with Shopware 6.6.

# 5.6.3
- Optimizes loading of the product data from the cache.

# 5.6.2
- Optimizes the setting of custom fields during the ordering process

# 5.6.1
- Fixes a possible problem where discount groups assigned to customers were no longer displayed in the account area.
- Fixes a problem where discounts were no longer correctly taken into account if they were assigned via the additional field "Discount group".
- Fixed issue with unit price not displaying when discount is active.

# 5.6.0
- Adds a plugin setting where an optimised display can be switched for a selected 5-column layout in the checkout process to optimise compatibility with other themes.

# 5.5.3
- Improved compatibility with other themes when using the alternative display in the shopping basket for discounts.

# 5.5.2
- Optimized Admin

# 5.5.1
- Optimization of cache invalidation for discount groups if all products were selected as product assignment.

# 5.5.0
- Further option for Discount group > Customer assignment (Apply to all customers (logged in or not logged in))
- Further option for Discount group > Customer assignment (Apply to all products)

# 5.4.0
- Added option for custom cart table display to plugin configuration.

# 5.3.7
- Fixes the migration issue.

# 5.3.6
- Optimizes updating of the database tables on installing / uninstalling of the plugin.

# 5.3.5
- Fixes possible problems when loading the discount groups in the account area.

# 5.3.4
- Improved compatibility with the caching mechanism of Shopware 6.5.

# 5.3.3
- Improved compatibility with Shopware >= 6.5 in the administration of the Shopware import/export module.

# 5.3.2
- Improved storefront compatibility with other plugins.

# 5.3.1
- Optimisation of cache invalidation for changes to discount groups.

# 5.3.0
- Allows the discount to be calculated from the purchase price.

# 5.2.0
- Adds a new default order confirmation with discounts from discount group mail template.

# 5.1.1
- Optimizes calculating of the list price across products for scale discounts in the cart.

# 5.1.0
- Adds a new plugin configuration for using scale discounts in the cart across products.
- Adds a new plugin configuration for displaying the current number of items in the same discount group from the shopping cart as a note on the product page.
- Adds a new display name field on the discount group detail page in the Administration.
- Optimizes loading of the scale discounts in the cart across products.

# 5.0.0
- Compatibility with Shopware 6.5.

# 4.1.0
- From now on it is possible to use the list price or the RRP (from ACRIS RRP plugin) as a basis for the discount calculation.
- From now on, the RRP price (from ACRIS RRP plugin) can also be displayed as a list price.

# 4.0.2
- Change of the plugin name and the manufacturer links.

# 4.0.1
- RRP based discount calculation corrected for Shopware versions at 6.4.18.0 and above.

# 4.0.0
- Optimized the plugin for Shopware versions at 6.4.18.0 and above.

# 3.4.1
- Calculates discount correctly when RRP based discount is higher than product price.

# 3.4.0
- Adds option "Prevents combination with ACRIS discount groups" at the promotion detail page inside of the conditions tab in the Administration.

# 3.3.0
- Now the plugin supports ACRIS RRP.

# 3.2.2
- Optimizes loading of the discount groups with dynamic product groups type of the Product assignment.

# 3.2.1
- Fixes a problem where discount groups are not loaded correctly to the products or dynamic product groups.

# 3.2.0
- Performance optimisation.

# 3.1.0
- Adds internal id field for discount groups.

# 3.0.1
- Optimizes product price calculation by discount groups.

# 3.0.0
- Compatibility with the lowest price of the last 30 days introduced in Shopware 6.4.10.0.
- Allows you to enter a minimum order quantity for a discount group.
- Correction of the basic price calculation when discounts become active.
- Improvement of the discount display in product detail page and product box.

# 2.3.0
- Added config for "Discount display in product box".
- Added config for "Discount display in product detail page".

# 2.2.0
- Enables better compatibility with the ACRIS B2B plugin.
- Performance optimisations.

# 2.1.3
- Fixes a possible problem in the ordering process

# 2.1.2
- Fixes a problem where discounts were not displayed in the shopping cart for graduated prices.
- The heading "My discounts" is only displayed in the account area if discounts are also available for the customer.
- Admin optimisations

# 2.1.1
- Optimisation of the admin view for the display options in the account area.

# 2.1.0
- Adds display option for discount groups in the account area.
- 
# 2.0.2
- Optimisation of the rounding of discounts for graduated prices.

# 2.0.1
- Optimizes plugin image.
- Improves compatibility with Shopware >= 6.4.10.0.
- Optimizes plugin color in administration.

# 2.0.0
- Adds discount groups in the shopping cart in the payload.
- Adds discount display in the shopping cart.
- Adds a plugin setting for rounding of the percentage discounts.

# 1.4.3
- Fixes a problem with the configuration of which price should be used to determine the discount.
- The option "List price available on the product: use this. - If there is no list price on the product: use the original product price as the list price." is now used as the default configuration for configuring which price to use to determine the discount. 
- Optimisation of the configuration which price should be used to determine the discount.

# 1.4.2
- Fixes a problem where discounts with a value of 0 were not taken into account.

# 1.4.1
- Fixes a problem where products were read from the cache with the wrong prices.
- Snippet optimisations in the admin.

# 1.4.0
- Optimizes snippets for product and customer discount group fields.

# 1.3.0
- Optimized discount group tabs at product and customer detail page in Administration.

# 1.2.0
- Adds import / export profile for discount groups.

# 1.1.2
- Clears cache after creating or saving of the discount group.

# 1.1.1
- Optimization in conjunction with the plugin for custom prices

# 1.1.0
- Optimize discount group data at listing page in Administration.

# 1.0.1
- Optimization of texts and displays in the admin module

# 1.0.0
- Release
