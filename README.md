# tourtimes_contao

*** In Entwicklung: noch nicht in einer Contao-Installation getestet ***

Regelmäßig wiederkehrende Termine wie Führungen für Contao (Bundle für Contao 5.7 oder neuer, PHP 8.5).
Schwester-Erweiterung von [opentimes_contao](https://github.com/derkosmonaut/opentimes_contao), gleiche Logik
für Pläne, Saisons und Ausnahmen, aber für einzelne Termine statt Öffnungsfenster.

## Funktionsumfang

- Beliebig viele **Angebote** (z. B. Führungen) mit Beschreibung, Bild, Treffpunkt, Dauer, Preis, Sprache und Ticketlink
- **Standard- und Saisonpläne** je Angebot (Zeitraum, jährlich wiederholbar, Priorität)
- **Zeitslots** je Plan, z. B. „Mo, Di, Mi 10:00–11:00“, optional mit festem Hinweis („auf Englisch“)
- **Ausnahmen** für Tage oder Zeiträume, für das ganze Angebot oder einzelne Slots:
  Ausfall, andere Uhrzeit, Hinweis (z. B. „Treffpunkt verlegt“) und Zusatztermin
- **Spontane Absagen** mit Hinweistext, für das ganze Angebot oder einzelne Slots, bis auf Weiteres oder mit Ende
- Drei Frontend-Module:
  - **Angebotsliste**: alle Angebote mit regelmäßigen Zeiten und den nächsten Terminen
  - **Termine heute**: die heutigen Termine chronologisch; ein Termin verschwindet, sobald er beginnt,
    mit einstellbarem **Versatz** entsprechend früher (z. B. 10 Minuten für den Infomonitor an der Kasse)
  - **Detailansicht**: ein Angebot mit allen Angaben und den nächsten Terminen
- Twig-Templates, Grundstil mit CSS-Variablen, deutsche und englische Texte

## Auflösungsreihenfolge

Die speziellere Ebene schlägt die allgemeinere:

1. Spontane Absage (schlägt alles, auch Zusatztermine)
2. Ausnahme (Ausfall schlägt Verschiebung, Hinweise werden gesammelt, Zusatztermine kommen hinzu)
3. Saisonplan (ersetzt den Standardplan vollständig, höhere Priorität gewinnt)
4. Standardplan

## Anwendung

**1. Angebot anlegen.** Unter „Inhalte → Führungen“ ein Angebot mit Titel, Texten und Details anlegen.
Die Dauer gilt für alle Termine ohne eigenes Ende.

**2. Pläne und Zeitslots.** Über das Symbol „Pläne“ einen Standardplan anlegen, optional Saisonpläne
(z. B. „Sommer“ vom 1.4. bis 31.10., jährlich). Je Plan Zeitslots anlegen: Wochentage anhaken
(mit Schnellauswahl), Beginn und optional Ende.

**3. Ausnahmen.** Für geplante Abweichungen an bestimmten Tagen. Art wählen:

| Art | Wirkung | Betrifft |
|---|---|---|
| Fällt aus | Termine fallen aus | ganzes Angebot oder ausgewählte Slots |
| Andere Uhrzeit | Termin findet zu neuer Uhrzeit statt, ohne Ende bleibt die Länge erhalten | ausgewählte Slots |
| Findet statt, mit Hinweis | Hinweis am Termin | ganzes Angebot oder ausgewählte Slots |
| Zusatztermin | zusätzlicher Termin zur angegebenen Uhrzeit | – |

**4. Spontane Absage.** Unter „Spontane Absagen“ Hinweistext und Beginn eintragen, das Ende ist optional.
Alle Termine, die im Zeitraum beginnen, fallen aus. Zum Aufheben „Aktiv“ abwählen oder löschen.

**5. Ausgabe.** Frontend-Modul anlegen und einbinden:

- *Angebotsliste*: Angebote auswählen (ohne Auswahl alle), Anzahl nächster Termine, Weiterleitungsseite zur Detailansicht
- *Termine heute*: Angebote, Versatz in Minuten, abgesagte Termine anzeigen oder ausblenden, Text ohne Termine
- *Detailansicht*: auf der Weiterleitungsseite einbinden; das Angebot kommt über den Alias in der URL

Abgesagte Termine erscheinen durchgestrichen mit „Fällt aus“ und Grund, wenn „Abgesagte Termine anzeigen“ aktiv ist.

### Infomonitor

Die Tagesliste blendet vergangene Termine auch ohne Neuladen aus (kleines Skript, prüft alle 15 Sekunden)
und lädt die Seite kurz nach Mitternacht neu. Der Seiten-Cache läuft bis zur nächsten Änderung ab
(30 Sekunden bis eine Stunde), Änderungen im Backend leeren ihn sofort.

## Entwicklung

```bash
composer install --no-plugins --ignore-platform-reqs
vendor/bin/phpunit
vendor/bin/phpstan analyse
```

Der Resolver (`src/Resolver/`) ist reines PHP ohne Contao und vollständig mit Tests abgedeckt.

## Lizenz

GPL-3.0-or-later
