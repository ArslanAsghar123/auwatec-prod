# 3.2.0
- Ändern der Logik des Tracking-Codes, um benutzerdefinierten Tracking-Code zu ermöglichen
- JavaScript-Array `ironMatomoDataLayer.track` ersetzt durch das Standard-`_paq`-Matomo-Objekt

# 3.1.0
- Konfiguration für "setConversionAttributionFirstReferrer" implementiert

# 3.0.0
- Major Update für Shopware 6.6 Kompatibilität

# 2.0.4
- Nächster versuch Webpack Builds kompatibel zu bekommen

# 2.0.3
- Kompatibilität zu unterschiedlichen Webpack builds herstellen
- Cookie Consent Registrierung am Ende der Tacking Initialisierung vornehmen.

# 2.0.2
- Aktuelle webpack Version für SW65

# 2.0.1
- Cookie Beschreibung für niederländisch

# 2.0.0
- Ersetze veraltete EntityRepositoryInterface durch EntityRepository für SW 6.,5 kompatibilität

# 1.2.4
- Cookie Beschreibung für niederländisch

# 1.2.3
- Fix: EntityRepository zu EntityRepositoryInterface für benutzerdefinierte Dekoration.
- EntityRepositoryInterface ist veraltet und wird in SW 6.5 entfernt werden

# 1.2.1
- Neu: Seo Kategorie bei Produktinformationen mit an Matomo übergeben

# 1.2.0
- Neu: Eigene Dateiennamen für Matomo Files (z.B. für Proxy Erweiterungen)

# 1.1.1
- Fix: Javascript Absturz auf Shopware 6.4.11.0

# 1.1.0
- Neu: Eigener Cookie Wert wird als Regular Expression Wert verwendet. Nützlich, wenn andere Cookie Consent Module (CookieFirst, CookieBot usw.) die Einstellungen in JSON codiertem String als Cookie Wert wegschreiben.

# 1.0.9
- Fix: Absturz abfangen, wenn ein anderes Plugin Parameter vom StorefrontRenderEvent verändert

# 1.0.8
- Fix: Cookie Beschreibung in Deutsch.

# 1.0.7
- Fix: Tracking Absturz mit Matomo kleiner als 3.14.0

# 1.0.6
- Fix: Tracking auf Bestellseite
- Neu: Tracking ohne Cookies

# 1.0.5
- Fix: Vertriebskanal-Fallback auf alle Vertriebskanäle, wenn URL oder Site-Id nicht definiert

# 1.0.4
- Fix: Matomo wird nicht auf dem Produktionssystem geladen

# 1.0.3
- Fix: Codestyles from Shopware

# 1.0.2
- Cookie Consent Manager

# 1.0.1
- Aktualisierung Plugin Logo

# 1.0.0
- Erste Version der Matomo-Integrationen für Shopware 6
