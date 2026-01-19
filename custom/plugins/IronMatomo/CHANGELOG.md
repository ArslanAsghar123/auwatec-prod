# 3.2.0
- Changing the logic of the tracking code to allow custom tracking code
- JavaScript array `ironMatomoDataLayer.track` replaced by the standard `_paq`-Matomo object

# 3.1.0
- Configuration for "setConversionAttributionFirstReferrer" implemented

# 3.0.0
- Major update for Shopware 6.6 compatibility

# 2.0.4
- Next try to get Webpack builds compatible

# 2.0.3
- Ensure compatibility with different webpack builds.
- Make cookie consent registration at the end of tacking initialization.

# 2.0.2
- Latest webpack version for SW65

# 2.0.1
- Cookie description for Dutch

# 2.0.0
- Replace deprecated EntityRepositoryInterface with EntityRepository für SW 6.5 compatibility

# 1.2.4
- Cookie description for Dutch

# 1.2.3
- Fix: EntityRepository to EntityRepositoryInterface for custom decoration.
- EntityRepositoryInterface is deprecated and will be removed in SW 6.5

# 1.2.2
- Fix: Crash when the product could not be read during category determination

# 1.2.1
- New: Seo category for product information also transferred to Matomo

# 1.2.0
- New: Own file names for Matomo files (e.g. for proxy extensions).

# 1.1.1
- Fix: Javascript crash on Shopware 6.4.11.0

# 1.1.0
- New: Own cookie value is used as Regular Expression value. Useful when other cookie consent modules (CookieFirst, CookieBot etc.) write away the settings in JSON encoded string as cookie value.

# 1.0.9
- Fix: catch crash when another plugin changes parameters from StorefrontRenderEvent

# 1.0.8
- Fix: Translate cookie description in german

# 1.0.7
- Fix: Tracking crashes with Matomo less than 3.14.0

# 1.0.6
- Fix: Tracking on order confirm page
- New: Tracking without Cookies

# 1.0.5
- Fix: Sales Channel Fallback to All Sales Channel if URL or Site Id not defined

# 1.0.4
- Fix: Matomo not loading on production system

# 1.0.3
- Fix: Codestyles from Shopware

# 1.0.2
- Cookie consent manager

# 1.0.1
- Update plugin logo

# 1.0.0
- First version of the Matomo integrations for Shopware 6
