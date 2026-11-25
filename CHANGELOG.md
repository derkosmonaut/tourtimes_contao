# Changelog

## Unveröffentlicht

### Geändert
- Angebotsliste: „Lesermodul“ ersetzt durch „In der Detailansicht ausblenden“ (auch für „Termine heute“). Liste und Detailansicht werden beide frei auf der Seite eingebunden; die Liste verschwindet, sobald ein Angebot aufgerufen ist. Eine Migration setzt den Haken überall, wo ein Lesermodul eingetragen war. **Das Detailmodul muss danach selbst auf der Seite eingebunden werden.**

### Behoben
- Detailansicht erschien doppelt, wenn sie zusätzlich zum Lesermodul der Liste auf der Seite stand. Ein Angebot wird jetzt je Seitenaufruf nur einmal ausgegeben

### Sonstiges
- Logo, Paket-Metadaten für den Contao Manager (`docs/package-metadata/`) und Screenshots im README

## 0.1.0 – 2026-11-20 (Release Candidate)

Erste Version, in einer Contao-5.7-Installation getestet.

### Angebote und Pläne
- Angebote mit Beschreibung, Bild, Treffpunkt, Dauer, Preis, Sprache und Ticketlink
- Standard- und Saisonpläne (Zeitraum, jährlich wiederholbar, Priorität) mit Zeitslots: Wochentage, Beginn, optional Ende und fester Hinweis
- Zeitraum je Angebot: „Termine ab“, „Termine bis“ und „Endet nach X Terminen“ (jeder Termin zählt, auch abgesagte und Zusatztermine); abgelaufene Angebote verschwinden aus der Angebotsliste
- Feiertage je Angebot (Bundesland): an gesetzlichen Feiertagen entfallen die regulären Termine; Zusatztermine und Ausnahmen mit anderer Uhrzeit oder Hinweis haben Vorrang

### Ausnahmen und Absagen
- Ausnahmen für Tage oder Zeiträume: Ausfall, andere Uhrzeit, Hinweis und Zusatztermin, für das ganze Angebot oder einzelne Slots; Hinweise für das ganze Angebot gelten auch für Zusatztermine
- Spontane Absagen mit Hinweistext, für das ganze Angebot oder einzelne Slots, bis auf Weiteres oder mit Ende

### Ausgabe
- Frontend-Modul „Angebotsliste“ mit regelmäßigen Zeiten und nächsten Terminen, optional mit Lesermodul (Liste und Detail auf einer Seite)
- Frontend-Modul „Termine heute“: vergangene Termine verschwinden (mit Versatz in Minuten), auch ohne Neuladen; optional der nächste Tag mit Terminen nach dem Hinweis; Neuladen nach Mitternacht (Infomonitor)
- Frontend-Modul „Angebot-Detailansicht“
- Strukturierte Daten: angezeigte Termine als schema.org-Events (abschaltbar)
- Sitemap: Detailseiten aller veröffentlichten, nicht abgelaufenen Angebote
- Cache bis zur nächsten Änderung, Cache-Tags für Änderungen im Backend
- Twig-Templates, Grundstil mit CSS-Variablen, deutsche und englische Texte

### Backend
- Vorschau beim Angebot: nächste zehn Termine mit Zustand und Hinweisen auf Unstimmigkeiten
- Schnellauswahl der Wochentage, Alias aus dem Titel
- Beim Duplizieren eines Angebots werden Ausnahmen und Absagen für einzelne Slots den kopierten Slots zugeordnet

### Entwicklung
- Tests (Resolver, Ausgabe, Datenbank mit SQLite, Feiertage), PHPStan und Release-Workflow in GitHub Actions
