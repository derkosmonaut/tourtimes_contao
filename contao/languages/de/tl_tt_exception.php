<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_tt_exception']['title'] = ['Bezeichnung', 'Nur für die Übersicht im Backend, z. B. „Domputz“.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['type'] = ['Art', 'Was an diesen Tagen passiert.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['start'] = ['Datum', 'Erster Tag der Ausnahme.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['stop'] = ['Bis', 'Optional. Leer = nur ein Tag.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['recurring'] = ['Jährlich wiederholen', 'Z. B. für Heiligabend.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['scope'] = ['Gilt für', 'Alle Termine des Angebots oder nur ausgewählte Zeitslots.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['slots'] = ['Zeitslots', 'Die betroffenen Zeitslots.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['starts'] = ['Beginn', 'Neue Uhrzeit bzw. Uhrzeit des Zusatztermins (HH:MM).'];
$GLOBALS['TL_LANG']['tl_tt_exception']['ends'] = ['Ende', 'Optional. Leer = gleiche Länge wie der Zeitslot bzw. Dauer des Angebots.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['note'] = ['Hinweis', 'Erscheint am Termin im Frontend, z. B. „Treffpunkt am Seitenportal“.'];

$GLOBALS['TL_LANG']['tl_tt_exception']['types'] = [
    'cancel' => 'Fällt aus',
    'shift' => 'Andere Uhrzeit',
    'note' => 'Findet statt, mit Hinweis',
    'extra' => 'Zusatztermin',
];
$GLOBALS['TL_LANG']['tl_tt_exception']['scopes'] = ['offer' => 'Alle Termine des Angebots', 'slots' => 'Ausgewählte Zeitslots'];

$GLOBALS['TL_LANG']['tl_tt_exception']['title_legend'] = 'Ausnahme';
$GLOBALS['TL_LANG']['tl_tt_exception']['date_legend'] = 'Datum';
$GLOBALS['TL_LANG']['tl_tt_exception']['scope_legend'] = 'Betroffene Termine';
$GLOBALS['TL_LANG']['tl_tt_exception']['time_legend'] = 'Uhrzeit';
$GLOBALS['TL_LANG']['tl_tt_exception']['note_legend'] = 'Hinweis';

$GLOBALS['TL_LANG']['tl_tt_exception']['new'] = ['Neue Ausnahme', 'Eine neue Ausnahme anlegen'];
$GLOBALS['TL_LANG']['tl_tt_exception']['edit'] = ['Ausnahme bearbeiten', 'Ausnahme ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_tt_exception']['copy'] = ['Ausnahme duplizieren', 'Ausnahme ID %s duplizieren'];
$GLOBALS['TL_LANG']['tl_tt_exception']['delete'] = ['Ausnahme löschen', 'Ausnahme ID %s löschen'];
$GLOBALS['TL_LANG']['tl_tt_exception']['show'] = ['Details', 'Details von Ausnahme ID %s'];
