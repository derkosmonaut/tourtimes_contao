# Changelog

## Unveröffentlicht

### Neu
- Feiertage je Angebot (Bundesland): an gesetzlichen Feiertagen entfallen die regulären Termine; Zusatztermine sowie Ausnahmen mit anderer Uhrzeit oder Hinweis haben Vorrang. Neue Spalte `holiday_region` in `tl_tt_offer`
- Vorschau im Backend beim Angebot: nächste zehn Termine mit Zustand und Hinweisen (kein Plan, leerer Plan, abgelaufen, noch nicht begonnen, Anzahl nicht erreicht, Absage aktiv/geplant, keine Termine in Sicht)
- Sitemap: Detailseiten aller veröffentlichten, nicht abgelaufenen Angebote (über die Weiterleitungsseiten der Module)
- Strukturierte Daten: angezeigte Termine als schema.org-Events in Angebotsliste und Detailansicht (abschaltbar, neue Spalte `tourtimes_jsonld` in `tl_module`)
- Termine heute: „Nächsten Tag anzeigen“ zeigt nach dem Hinweis die Termine des nächsten Tages mit Terminen, auch ohne Neuladen (neue Spalte `tourtimes_show_next` in `tl_module`)
- Angebotsliste: Einstellung „Lesermodul“ (Contao-Standard). Ist ein Angebot aufgerufen, erscheint statt der Liste die Detailansicht, Liste und Detail passen so auf eine Seite. Neue Spalte `tourtimes_readerModule` in `tl_module`
- Zeitraum je Angebot: „Termine ab“, „Termine bis“ und „Endet nach X Terminen“. Abgelaufene Angebote verschwinden aus der Angebotsliste, die Detailansicht zeigt den Zeitraum. Neue Spalten `date_from`, `date_until`, `max_dates` in `tl_tt_offer` (Datenbank aktualisieren)

### Behoben
- Detailansicht: 404, weil der Alias aus der URL als unbenutzt galt
- Detailansicht: Absturz durch `StringUtil::inputEncodedToPlainText()` (in Contao 5 über `HtmlDecoder`)
- Detailansicht: „Anzahl nächster Termine“ = 0 blendet die Termine aus; Einstellungen unter eigener Überschrift

### Neu
- Angebote mit Beschreibung, Bild, Treffpunkt, Dauer, Preis, Sprache und Ticketlink
- Standard- und Saisonpläne mit Zeitslots (Wochentage, Beginn, optional Ende und Hinweis)
- Ausnahmen: Ausfall, andere Uhrzeit, Hinweis und Zusatztermin, für das ganze Angebot oder einzelne Slots
- Spontane Absagen mit Hinweistext, für das ganze Angebot oder einzelne Slots
- Frontend-Module „Angebotsliste“, „Termine heute“ (mit Versatz, für Infomonitore) und „Angebot-Detailansicht“
- Twig-Templates, Grundstil mit CSS-Variablen, deutsche und englische Texte
- Tests (Resolver, Ausgabe, Datenbank mit SQLite), PHPStan und Release-Workflow in GitHub Actions
