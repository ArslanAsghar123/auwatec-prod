# 4.1.3
- Fix: Namespace Typo Error des neuen Events

# 4.1.2
- New: Event zur LexiconReplacer Componente hinzugefügt mit Keywords Array
- Feature: Performance verbessert

# 4.1.1
- Fix: Problem bei bestimmten Produkt-Datensätzen beim Import der Verknüpfung von migrierten Lexikon-Einträgen 

# 4.1.0
- Fix: Update Fehler bei einer hohen MySql Version behoben
- Fix: Fehler beim Laden eines JS Script behoben
- Fix: Fehler behoben, wenn bestimmte Produkte in den Warenkorb gelegt wurden

# 4.0.9
- Feature: Verbesserungen für die Barrierefreiheit hinzugefügt
- Fix: bei Produktseite im CMS-Format wurden Eigenschaften nicht berücksichtigt - behoben

# 4.0.8
- Feature: CSS Klasse is-lexicon in den container-main versetzt

# 4.0.7
- Neu: Migrationsfunktion Shopware 5 ->  Shopware 6 eingebaut
- Feature: img-Tags werden jetzt von der Ersetzung-Funktion ausgenommen

# 4.0.6
- Fix: Lexikon Navigation in Mobile Phone Ansicht
- Fix: neuer Lexikon-Eintrag nicht mehr ohne Titel oder Keyword speicherbar

# 4.0.5
- Feature: Erweiterte Metatitel jetzt optional abschaltbar in Plugin Settings
- Fix: Admin snippets in SW 6.6.4 error

## 4.0.4
- Feature: Lexikon Breadcrumb in Struktur und Klassen dem SW Breadcrumb angeglichen
- Fix: Metatitel zurück in ursprüngliche Form
- Fix: SW CMS Video Elemente auf Lexikon Seiten

## 4.0.3
- Feature: Breadcrumb-Navigationspfade werden jetzt von Google erkannt

## 4.0.2
- Neu: Option, auf Seo Urls zu verzichten bei Modal-Fenster Ersetzung für bessere Ladezeiten

## 4.0.1
- Fix: Fehler bei sprachabhängigen Lexikon-Seiten-Links

## 4.0.0
- Anpassung an Shopware 6.6

## 3.1.6
- Fix: Kleine CSS Anpassungen im Frontend
- Neu: Optionale Erweiterung der Storefront Suche um Lexikon Einträge

## 3.1.5
- Fix: Hinweise in den Plugin-Einstellungen verbessert
- Neu: Verlinkung der Lexikoneinträge zu Modal-Fenster jetzt auch in Mobil Ansicht

## 3.1.4
- Neu: Spezifikationen für Robots-Meta-Tag optional in Plugin-Einstellungen wählbar für Lexikon Übersichtsseite
- Neu: Spezifikationen für Robots-Meta-Tag optional in Plugin-Einstellungen wählbar für Lexikon Detailseite
- Neu: Spezifikationen für Robots-Meta-Tag optional in Plugin-Einstellungen wählbar für Lexikon Listing-Seite
- Neu: Spezifikationen für Robots-Meta-Tag optional in Plugin-Einstellungen wählbar für Lexikon Inhaltsverzeichnisseite

## 3.1.3
* Fix: Laden der Tabelle der Lexikoneinträge verbessert
* Fix: Verbesserung des Listings der Lexikon-Seiten in den Erlebniswelten
* Fix: Seo url Erstellung für Detail Seiten berücksichtigt jetzt die Sprache
* Neu: Möglichkeit der zuweisung von produkten zu Lexikoneinträgen unter Produktdetail-Spezifikationen

## 3.1.2
- Fix: Filter Button auf dem Smartphone korrigiert

## 3.1.1
- Fix: Problem beim Update der Datenbank behoben
- Fix: Ausfiltern leerer Blöcke auf den Lexikon-Seiten verbessert

## 3.1.0
- Neu: Layout der Lexikon-Seiten in Erlebniswelten-CMS-Seiten geändert: 
- Neu: CMS-Blöcke und -Elemente für Lexikon-Seiten 
- Neu: CMS Standardseiten für Lexikon-Übersicht, Inhalt, Listing und Details

## 3.0.2
- Fix: Metadaten verbessert 
- Fix: Keine SEO-URL-Erstellung, wenn das Plugin im Verkaufskanal nicht aktiv ist 
- Fix: Keine Sitemap-URL-Erstellung, wenn das Plugin im Vertriebskanal nicht aktiv ist
- Fix: Fehler fixed bezüglich Shopware 6.5 Anpassung

## 3.0.1
* Fehler fixed bezüglich Shopware 6.5 Anpassung

## 3.0.0
* Anpassung an SW 6.5

## 2.0.9
* kleinen Fehler beim Loggen der Fehler beim Generieren der SEO Url's behoben
* kleinen Fehler bei der Generierung der SEO-Url's behoben
* Information zu SEO-Url's hinzugefügt in den Plugin Einstellungen

## 2.0.8
* Lexikon SEO URLs für die Übersicht und das Inhaltsverzeichnis sind nun übersetzbar

## 2.0.7
* Verbesserung bei Lexikon Ersetzungen in Produktseiten mit CMS Layout
* Neuer Twig Filter cbax_lexicon_replace für Lexikon Ersetzungen, einsetzbar überall im Storefront Template {{ text|cbax_lexicon_replace|raw }}

## 2.0.6
* Fehler beim Scheduled Task behoben

## 2.0.5
* Fehler behoben, der beim Erstellen eines neuen Eintrags im Frontend auftritt, wenn man die Beschreibung leer lässt

## 2.0.4
* Verbesserung beim Speichern neuer Lexikon Einträge inklusive Test der Keywords auf schon Vorhandensein
* Performance Verbesserung
* Sortierung der Lexikoneinträge innerhalb eines Buchstaben
* Neue Funktion in der Lexikon-Tabelle zum Duplizieren eines Eintrages
* Lexikon Urls für Sitemap jetzt optional
* Verbesserungen bei der Seo Url Erstellung
* Anpassungen bei der Modal-Funktionalität für SW 6.4.11
* Lexikon Ersetzungen auch in Produktseiten mit Erlebniswelt Layout
* Lexikon Ersetzungen auch in QuickView-Modal Produktbeschreibung
* Lexikon Ersetzungen auch in auswählbaren Produkt Zusatzfeldern

## 2.0.3
* Fehler in den Lexikon Entities beseitigt
* Performance Verbesserung

## 2.0.2
* Anzeige der Zusatzfelder an Shopware 6.4 angepasst

## 2.0.1
* Sitemap Url Erstellung an Shopware 6.4 angepasst

## 2.0.0
* Anpassungen für das Shopware 6.4 Update

## 1.1.3
* 2 unnötige Spalten in der Verknüpfungstabelle Produkt - Lexikoneintrag entfernt

## 1.1.2
* Fehler bei Lexikon Tooltips off-canvas behoben

## 1.1.1
* Probleme bei Shops mit Subdomains behoben

## 1.1.0
* Lexikon Tooltips auch in off-canvas Texten ermöglicht
* Einordnung von Umlauten geändert

## 1.0.9
* beim Datumsabgleich zur Bestimmung, ob ein Eintrag in der Zukunft erst freigegeben werden soll, wird nun direkt per Timestamp geprüft

## 1.0.8
* Performance-Verbesserung für Scheduled Task

## 1.0.7
* Kleinen Fehler bei leerem Einstelldatum korrigiert

## 1.0.6
* Anpassungen zur Kompatibilität an Shopware 6.3 gemacht

## 1.0.5
* Fehler bei der Verwendung von Unterverzeichnissen für das Routing behoben

## 1.0.4
* Fehler beim Zuweisen der Default Config behoben

## 1.0.3
* Neue Option: Tooltip anstelle vom Modal-Fenster
* Fehler bei https Protocol beseitigt

## 1.0.2
* Neue Erlebniswelten-Komponente für alle Erlebniswelten-Typen hinzugefügt

## 1.0.1

* Fehler auf dem Kategorie-Event behoben

## 1.0.0

* ** Erste Veröffentlichung des Plugin **
