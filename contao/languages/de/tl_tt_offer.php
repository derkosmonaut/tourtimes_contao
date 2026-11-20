<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_tt_offer']['title'] = ['Titel', 'Name des Angebots, z. B. „Domführung“.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['alias'] = ['Alias', 'URL-Teil der Detailseite. Bleibt das Feld leer, wird er aus dem Titel erzeugt.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['teaser'] = ['Kurzbeschreibung', 'Erscheint in der Liste.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['description'] = ['Beschreibung', 'Ausführlicher Text für die Detailansicht.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['singleSRC'] = ['Bild', 'Optionales Bild zum Angebot.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['meeting_point'] = ['Treffpunkt', 'Z. B. „Hauptportal“.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['duration'] = ['Dauer in Minuten', 'Gilt für alle Termine ohne eigenes Ende. 0 = ohne Ende.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['price'] = ['Preis', 'Freitext, z. B. „12 €, ermäßigt 8 €“.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['language'] = ['Sprache', 'Z. B. „Deutsch“. Abweichungen einzelner Termine als Hinweis am Zeitslot.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['ticket_url'] = ['Ticketlink', 'Adresse des Ticketshops.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['ticket_label'] = ['Linktext', 'Leer = „Tickets“.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['published'] = ['Veröffentlicht', 'Nur veröffentlichte Angebote erscheinen im Frontend.'];

$GLOBALS['TL_LANG']['tl_tt_offer']['date_from'] = ['Termine ab', 'Optional. Vorher finden keine Termine statt.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['date_until'] = ['Termine bis', 'Optional. Danach finden keine Termine statt und das Angebot verschwindet aus der Liste.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['max_dates'] = ['Endet nach Terminen', 'Optional, z. B. 5. Gezählt wird ab „Termine ab“, jeder Termin zählt mit (auch abgesagte und Zusatztermine). 0 = unbegrenzt.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['holiday_region'] = ['Keine Termine an Feiertagen', 'An gesetzlichen Feiertagen dieses Bundeslandes entfallen die regulären Termine. Leer = Termine auch an Feiertagen. Zusatztermine und Ausnahmen (andere Uhrzeit, Hinweis) für den Tag haben Vorrang.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['regions'] = [
    'DE' => 'Nur bundesweite Feiertage',
    'BW' => 'Baden-Württemberg', 'BY' => 'Bayern', 'BE' => 'Berlin', 'BB' => 'Brandenburg',
    'HB' => 'Bremen', 'HH' => 'Hamburg', 'HE' => 'Hessen', 'MV' => 'Mecklenburg-Vorpommern',
    'NI' => 'Niedersachsen', 'NW' => 'Nordrhein-Westfalen', 'RP' => 'Rheinland-Pfalz', 'SL' => 'Saarland',
    'SN' => 'Sachsen', 'ST' => 'Sachsen-Anhalt', 'SH' => 'Schleswig-Holstein', 'TH' => 'Thüringen',
];
$GLOBALS['TL_LANG']['tl_tt_offer']['period_legend'] = 'Zeitraum';
$GLOBALS['TL_LANG']['tl_tt_offer']['tourtimes_preview'] = ['Vorschau: nächste Termine', ''];
$GLOBALS['TL_LANG']['tl_tt_offer']['preview_legend'] = 'Vorschau';
$GLOBALS['TL_LANG']['tl_tt_offer']['warnings'] = [
    'no_plan' => 'Es ist noch kein Plan und kein Zusatztermin angelegt. Im Frontend erscheinen keine Termine.',
    'default_empty' => 'Der Standardplan enthält keinen gültigen Zeitslot.',
    'season_empty' => 'Der Saisonplan „%s“ hat keinen Zeitslot. In dieser Saison finden keine Termine statt.',
    'ended' => 'Das Angebot ist abgelaufen (Zeitraum vorbei oder letzter Termin begonnen) und erscheint nicht mehr in der Angebotsliste.',
    'not_started' => 'Das Angebot beginnt erst am %s.',
    'count_not_reached' => 'Die %s Termine werden im Zeitraum nicht erreicht. Es gelten nur die Termine bis „Termine bis“.',
    'cancellation_active' => 'Spontane Absage aktiv: %s',
    'cancellation_planned' => 'Spontane Absage geplant: %s',
    'no_upcoming' => 'In den nächsten %s Tagen gibt es keinen Termin.',
];
$GLOBALS['TL_LANG']['tl_tt_offer']['title_legend'] = 'Angebot';
$GLOBALS['TL_LANG']['tl_tt_offer']['text_legend'] = 'Texte';
$GLOBALS['TL_LANG']['tl_tt_offer']['image_legend'] = 'Bild';
$GLOBALS['TL_LANG']['tl_tt_offer']['details_legend'] = 'Details';
$GLOBALS['TL_LANG']['tl_tt_offer']['ticket_legend'] = 'Tickets';
$GLOBALS['TL_LANG']['tl_tt_offer']['publish_legend'] = 'Veröffentlichung';

$GLOBALS['TL_LANG']['tl_tt_offer']['new'] = ['Neues Angebot', 'Ein neues Angebot anlegen'];
$GLOBALS['TL_LANG']['tl_tt_offer']['edit'] = ['Angebot bearbeiten', 'Angebot ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_tt_offer']['schedules'] = ['Pläne', 'Standard- und Saisonpläne von Angebot ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['exceptions'] = ['Ausnahmen', 'Ausnahmen von Angebot ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['cancellations'] = ['Spontane Absagen', 'Spontane Absagen von Angebot ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['copy'] = ['Angebot duplizieren', 'Angebot ID %s duplizieren'];
$GLOBALS['TL_LANG']['tl_tt_offer']['toggle'] = ['Veröffentlichen', 'Angebot ID %s veröffentlichen/verbergen'];
$GLOBALS['TL_LANG']['tl_tt_offer']['delete'] = ['Angebot löschen', 'Angebot ID %s löschen'];
$GLOBALS['TL_LANG']['tl_tt_offer']['show'] = ['Details', 'Details von Angebot ID %s'];
