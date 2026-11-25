<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

/*
 * Angebot = die Führung (oder ein anderer regelmäßiger Termin).
 * Darunter hängen Pläne (Standard/Saison) mit Zeitslots, Ausnahmen und spontane Absagen.
 * Bewusst nicht "Event" genannt, um Verwechslungen mit dem Contao-Kalender zu vermeiden.
 */
$GLOBALS['TL_DCA']['tl_tt_offer'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_tt_schedule', 'tl_tt_exception', 'tl_tt_cancellation'],
        'enableVersioning' => true,
        'sql' => [
            'keys' => ['id' => 'primary', 'alias' => 'index', 'published' => 'index'],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['title'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['title', 'meeting_point'],
            'format' => '%s <span class="tl_gray">%s</span>',
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => ['href' => 'act=edit', 'icon' => 'edit.svg'],
            'schedules' => ['href' => 'table=tl_tt_schedule', 'icon' => 'children.svg'],
            'exceptions' => ['href' => 'table=tl_tt_exception', 'icon' => 'bundles/derkosmonauttourtimes/icons/calendar-day.svg'],
            'cancellations' => ['href' => 'table=tl_tt_cancellation', 'icon' => 'bundles/derkosmonauttourtimes/icons/bell.svg'],
            'copy' => ['href' => 'act=copy', 'icon' => 'copy.svg'],
            'toggle' => ['href' => 'act=toggle&amp;field=published', 'icon' => 'visible.svg'],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        'default' => '{title_legend},title,alias;{text_legend},teaser,description;{image_legend},singleSRC;{details_legend},time_mode,meeting_point,duration,price,language;{period_legend},date_from,date_until,max_dates,holiday_region;{ticket_legend},ticket_url,ticket_label;{publish_legend},published;{preview_legend},tourtimes_preview',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'title' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        // URL-Teil der Detailseite, wird aus dem Titel erzeugt (AliasListener)
        'alias' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['rgxp' => 'alias', 'doNotCopy' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) BINARY NOT NULL default ''",
        ],
        'teaser' => [
            'inputType' => 'textarea',
            'search' => true,
            'eval' => ['maxlength' => 1000, 'tl_class' => 'clr'],
            'sql' => 'text NULL',
        ],
        'description' => [
            'inputType' => 'textarea',
            'search' => true,
            'eval' => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'clr'],
            'explanation' => 'insertTags',
            'sql' => 'mediumtext NULL',
        ],
        'singleSRC' => [
            'inputType' => 'fileTree',
            'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr'],
            'sql' => 'binary(16) NULL',
        ],
        // start = feste Startzeit (Führung), window = Zeitfenster, Start jederzeit (z. B. Audioguide)
        'time_mode' => [
            'inputType' => 'select',
            'options' => ['start', 'window'],
            'reference' => &$GLOBALS['TL_LANG']['tl_tt_offer']['time_modes'],
            'filter' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(8) NOT NULL default 'start'",
        ],
        'meeting_point' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        // Minuten; gilt für alle Slots ohne eigenes Ende
        'duration' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'natural', 'maxlength' => 4, 'tl_class' => 'w50'],
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
        // Freitext, z. B. "12 €, ermäßigt 8 €"
        'price' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'language' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        // Zeitraum des Angebots: außerhalb gibt es keine Termine, danach verschwindet es aus der Liste
        'date_from' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'date_until' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'save_callback' => [['Derkosmonaut\\TourTimesBundle\\Dca\\DateRangeCallback', 'validateStop']],
            'sql' => 'int(10) unsigned NULL',
        ],
        // zählt ab "Termine ab" jeden Termin, auch abgesagte und Zusatztermine (0 = unbegrenzt)
        'max_dates' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'natural', 'maxlength' => 4, 'tl_class' => 'w50'],
            'save_callback' => [['Derkosmonaut\\TourTimesBundle\\Dca\\DateRangeCallback', 'requireStartForCount']],
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
        // an gesetzlichen Feiertagen dieses Bundeslandes keine regulären Termine (leer = Feiertage egal)
        'holiday_region' => [
            'inputType' => 'select',
            'options' => ['DE', 'BW', 'BY', 'BE', 'BB', 'HB', 'HH', 'HE', 'MV', 'NI', 'NW', 'RP', 'SL', 'SN', 'ST', 'SH', 'TH'],
            'reference' => &$GLOBALS['TL_LANG']['tl_tt_offer']['regions'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'ticket_url' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 2048, 'dcaPicker' => true, 'tl_class' => 'w50'],
            'sql' => 'text NULL',
        ],
        // leer = Standardtext "Tickets"
        'ticket_label' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        // nur Anzeige, wird über OfferPreviewListener gefüllt
        'tourtimes_preview' => [
            'eval' => ['tl_class' => 'clr', 'doNotShow' => true],
        ],
        'published' => [
            'inputType' => 'checkbox',
            'toggle' => true,
            'filter' => true,
            'default' => true,
            'eval' => ['doNotCopy' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
    ],
];
