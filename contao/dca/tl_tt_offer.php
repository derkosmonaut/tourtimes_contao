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
        'default' => '{title_legend},title,alias;{text_legend},teaser,description;{image_legend},singleSRC;{details_legend},meeting_point,duration,price,language;{ticket_legend},ticket_url,ticket_label;{publish_legend},published',
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
