<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

/*
 * Geplante Ausnahme für ein Datum oder einen Datumsbereich:
 *   cancel = Termine fallen aus
 *   shift  = ausgewählte Slots finden zu einer anderen Uhrzeit statt
 *   note   = Termine finden mit Hinweis statt (z. B. "Treffpunkt verlegt")
 *   extra  = Zusatztermin
 * Bereich "offer" betrifft alle Termine des Angebots, "slots" nur die ausgewählten.
 */
$GLOBALS['TL_DCA']['tl_tt_exception'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_tt_offer',
        'sql' => [
            'keys' => ['id' => 'primary', 'pid' => 'index'],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['start'],
            'headerFields' => ['title', 'meeting_point'],
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['title', 'type', 'start'],
            'format' => '%s <span class="tl_gray">(%s, %s)</span>',
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
            'copy' => ['href' => 'act=copy', 'icon' => 'copy.svg'],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'scope'],
        'default' => '{title_legend},title,type;{date_legend},start,stop,recurring;{scope_legend},scope;{note_legend},note',
        'cancel' => '{title_legend},title,type;{date_legend},start,stop,recurring;{scope_legend},scope;{note_legend},note',
        'shift' => '{title_legend},title,type;{date_legend},start,stop,recurring;{scope_legend},slots;{time_legend},starts,ends;{note_legend},note',
        'note' => '{title_legend},title,type;{date_legend},start,stop,recurring;{scope_legend},scope;{note_legend},note',
        'extra' => '{title_legend},title,type;{date_legend},start,stop,recurring;{time_legend},starts,ends;{note_legend},note',
    ],
    'subpalettes' => [
        'scope_slots' => 'slots',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_tt_offer.title',
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        // interne Bezeichnung für die Liste im Backend
        'title' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'inputType' => 'select',
            'options' => ['cancel', 'shift', 'note', 'extra'],
            'reference' => &$GLOBALS['TL_LANG']['tl_tt_exception']['types'],
            'filter' => true,
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'cancel'",
        ],
        'start' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'mandatory' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(10) unsigned NULL',
        ],
        // leer = nur ein einzelner Tag
        'stop' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'save_callback' => [['Derkosmonaut\\TourTimesBundle\\Dca\\DateRangeCallback', 'validateStop']],
            'sql' => 'int(10) unsigned NULL',
        ],
        'recurring' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'scope' => [
            'inputType' => 'radio',
            'options' => ['offer', 'slots'],
            'reference' => &$GLOBALS['TL_LANG']['tl_tt_exception']['scopes'],
            'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "varchar(8) NOT NULL default 'offer'",
        ],
        // Optionen: alle Slots des Angebots (SlotOptionsListener)
        'slots' => [
            'inputType' => 'checkbox',
            'eval' => ['multiple' => true, 'mandatory' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        // neue Uhrzeit (Verschiebung) bzw. Uhrzeit des Zusatztermins
        'starts' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 5, 'placeholder' => '11:30', 'tl_class' => 'w50'],
            'save_callback' => [['Derkosmonaut\\TourTimesBundle\\Dca\\TimeInputCallback', 'validateOpens']],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        // leer = Länge des Slots bzw. Dauer des Angebots
        'ends' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 5, 'placeholder' => '12:30', 'tl_class' => 'w50'],
            'save_callback' => [['Derkosmonaut\\TourTimesBundle\\Dca\\TimeInputCallback', 'validateCloses']],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        // öffentlicher Hinweis am Termin
        'note' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['maxlength' => 255, 'tl_class' => 'long clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
