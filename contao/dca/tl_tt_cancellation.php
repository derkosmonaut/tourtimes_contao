<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

/*
 * Spontane Absage mit höchster Priorität: Termine, die im Zeitraum beginnen, fallen aus.
 * Ohne Ende gilt sie, bis sie deaktiviert oder gelöscht wird. Ein Beginn in der
 * Zukunft ist erlaubt. Gilt fürs ganze Angebot oder nur für ausgewählte Slots.
 */
$GLOBALS['TL_DCA']['tl_tt_cancellation'] = [
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
            'fields' => ['start DESC'],
            'headerFields' => ['title', 'meeting_point'],
            'panelLayout' => 'filter;limit',
        ],
        'label' => [
            'fields' => ['message', 'start'],
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
            'toggle' => ['href' => 'act=toggle&amp;field=active', 'icon' => 'visible.svg'],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        '__selector__' => ['scope'],
        'default' => '{cancellation_legend},message,active;{time_legend},start,stop;{scope_legend},scope',
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
        'message' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'active' => [
            'inputType' => 'checkbox',
            'toggle' => true,
            'default' => true,
            'filter' => true,
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'start' => [
            'inputType' => 'text',
            'default' => time(),
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'mandatory' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(10) unsigned NULL',
        ],
        // leer = bis auf Weiteres
        'stop' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(10) unsigned NULL',
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
    ],
];
