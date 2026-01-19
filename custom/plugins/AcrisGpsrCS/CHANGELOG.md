# 2.14.9
- GPSR information is now also loaded for Shopware product exports for additional sales channels and is available in the product data under product.extensions.acrisGpsr.

# 2.14.8
- Code optimizations.

# 2.14.7
- Improved compatibility with ACRIS Manufacturer plugin.

# 2.14.6
- Code optimizations.

# 2.14.5
- Code optimizations.

# 2.14.4
- Improved compatibility of the admin view with Shopware 6.6.10.0.

# 2.14.3
- Fixed an issue where data wasn't properly removed when uninstalling the plugin.

# 2.14.2
- Fixes an issue with not being able to uninstall the plugin.

# 2.14.1
- JTL WaWi compatibility improved.

# 2.14.0
- Improves plugin compatibility.
- Adds new custom fields for ERP.
- Adds new plugin configuration for using the GPSR data imported by ERP.

# 2.13.5
- Fixes broken info text display in the plugin configuration.

# 2.13.4
- Fixed an inheritance issue with the product tab title in admin.

# 2.13.3
- Added default values for config.

# 2.13.2
- Bugfix fixed product tab title in admin.

# 2.13.1
- Bugfix fixed product tab title in admin.

# 2.13.0
- Added configuration for set and show/hide GPSR Product Type;Product Warning Note,Product Safety Note,Product Important infomration.

# 2.12.0
- Added configuration for display gpsr information at product in admin.

# 2.11.3
- Fixed the inheritance of GPSR fields on the admin product detail page.

# 2.11.2
- Bugfix fixed SEO url change.

# 2.11.1
- Adds validation for the manufacturer Shopware link field of the "ACRIS GPSR: Manufacturer" import / export profile.

# 2.11.0
- Adds new "ACRIS GPSR: Manufacturer" import / export profile.

# 2.10.0
- Added telephone link and email link.

# 2.9.1
- Bugfix fixed product GPSR fileds inheritance.

# 2.9.0
- Adds new "ACRIS GPSR: Product" import / export profile.

# 2.8.1
- Bugfix fixed missing column error
- Bugfix fixed tab error

# 2.8.0
- Added configuration to show document as list.
- Added configuration to show and set document title.

# 2.7.3
- Bugfix removed unnecessary columns.

# 2.7.2
- Bugfix fixed edit close title for a file in admin.
- Bugfix fixed compatibility with AcrisProductDownload.

# 2.7.1
- Bugfix fixed to show tab when content is empty for notes/warning.

# 2.7.0
- It is now possible to import a manufacturer.

# 2.6.1
- Added check if custom filed exits during install.

# 2.6.0
- It is possible now to add a file name which should be shown on the frontend.

# 2.5.2
- Optimization of the modal window integration.

# 2.5.1
- Linked files are no longer downloaded immediately in the storefront, but are opened in a new tab.
- For linked files, the file name is now displayed in the storefront without an additional parameter.
- The GPSR document upload in the admin has been removed from the manufacturer again.
- Fixes an issue where the responsible person could not be opened in the GPSR module in the admin.

# 2.5.0
- Added option to upload documents for GPSR information.

# 2.4.1
- Fixed hiding GPSR information in mobile views.
- A heading is now displayed for the product type.

# 2.4.0
- Allows GPSR information specified on the manufacturer or product to be hidden via the plugin settings.
- Fixes a problem where tabs were displayed twice if the display was configured in a separate GPSR tab.
- Allows you to configure the display position of the GPSR tab.

# 2.3.8
- Fixed offcanvas GPSR content not scrollable on mobile viewports.

# 2.3.7
- Improved compatibility with plugins on product pages does not allow a preselected variant.
- Fixes a problem when opening variants in the administration.

# 2.3.6
- Fixes a problem when loading GPSR information for products and manufacturers from the shop default language as fallback if no content was specified for the respective language.

# 2.3.5
- Fixes a possible problem when loading the GPSR information from the manufacturer in Admin.

# 2.3.4
- Fixed issue when creating new manufacturer in administration.

# 2.3.3
- New options for configuring GPRS information in tabs

# 2.3.2
- Improved tab content layout in Storefront.

# 2.3.1
- Fixed manufacturer data not saving in administration.

# 2.3.0
- Added display settings for GPSR loaded from manufacturers and products in plugin configuration.

# 2.2.1
- Fixes a possible problem if custom fields are not filled
- Layout of the warning/safety instructions is adopted in the GPSR tab
- Twig blocks have been added

# 2.2.0
- New setting to display the GPSR information (manufacturer, responsible person, warning/safety notes) all in one tab

# 2.1.1
- Code optimizations in the administration and in the storefront
- Caching has been implemented

# 2.1.0
- Manufacturer link behavior has been implemented
- Optimization of the administration

# 1.0.0
- Release
