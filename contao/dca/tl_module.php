<?php

declare(strict_types=1);

$dca = &$GLOBALS['TL_DCA']['tl_module'];

// Liste aller Angebote mit regelmäßigen Zeiten und den nächsten Terminen
$dca['palettes']['tourtimes_list'] =
    '{title_legend},name,headline,type;'
    .'{tourtimes_legend},tourtimes_offers,tourtimes_upcoming,tourtimes_show_cancelled,tourtimes_jsonld,jumpTo,tourtimes_readerModule;'
    .'{image_legend:hide},imgSize;'
    .'{template_legend:hide},customTpl;'
    .'{protected_legend:hide},protected;'
    .'{expert_legend:hide},guests,cssID';

// Termine des aktuellen Tages, z. B. für den Infomonitor an der Kasse
$dca['palettes']['tourtimes_today'] =
    '{title_legend},name,headline,type;'
    .'{tourtimes_legend},tourtimes_offers,tourtimes_offset,tourtimes_show_cancelled,tourtimes_empty,tourtimes_show_next,jumpTo;'
    .'{template_legend:hide},customTpl;'
    .'{protected_legend:hide},protected;'
    .'{expert_legend:hide},guests,cssID';

// Detailansicht eines Angebots (Alias aus der URL)
$dca['palettes']['tourtimes_reader'] =
    '{title_legend},name,type;'
    .'{tourtimes_reader_legend},tourtimes_upcoming,tourtimes_show_cancelled,tourtimes_jsonld;'
    .'{image_legend:hide},imgSize;'
    .'{template_legend:hide},customTpl;'
    .'{protected_legend:hide},protected;'
    .'{expert_legend:hide},guests,cssID';

$dca['fields']['tourtimes_offers'] = [
    'inputType' => 'checkboxWizard',
    'foreignKey' => 'tl_tt_offer.title',
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
];

$dca['fields']['tourtimes_upcoming'] = [
    'inputType' => 'text',
    'default' => 3,
    'eval' => ['rgxp' => 'natural', 'maxlength' => 3, 'tl_class' => 'w50'],
    'sql' => 'smallint(5) unsigned NOT NULL default 3',
];

$dca['fields']['tourtimes_offset'] = [
    'inputType' => 'text',
    'default' => 0,
    'eval' => ['rgxp' => 'natural', 'maxlength' => 3, 'tl_class' => 'w50'],
    'sql' => 'smallint(5) unsigned NOT NULL default 0',
];

$dca['fields']['tourtimes_show_cancelled'] = [
    'inputType' => 'checkbox',
    'default' => true,
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default '1'",
];

$dca['fields']['tourtimes_empty'] = [
    'inputType' => 'text',
    'eval' => ['maxlength' => 255, 'tl_class' => 'clr long'],
    'sql' => "varchar(255) NOT NULL default ''",
];

// schema.org-Events der angezeigten Termine ausgeben
$dca['fields']['tourtimes_jsonld'] = [
    'inputType' => 'checkbox',
    'default' => true,
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default '1'",
];

// Termine heute: nach dem Hinweis den nächsten Tag mit Terminen zeigen
$dca['fields']['tourtimes_show_next'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];

// Lesermodul: ist ein Angebot aufgerufen, zeigt die Liste stattdessen die Detailansicht (Optionen: ReaderModuleOptionsListener)
$dca['fields']['tourtimes_readerModule'] = [
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

unset($dca);
