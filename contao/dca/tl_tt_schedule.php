<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

/*
 * Plan = Wochenraster eines Angebots. Typ "default" ist der Standardplan,
 * Typ "season" ein Saisonplan mit Gültigkeitszeitraum und Priorität.
 * Die einzelnen Termine hängen als tl_tt_slot daran.
 */
$GLOBALS['TL_DCA']['tl_tt_schedule'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_tt_offer',
        'ctable' => ['tl_tt_slot'],
        'sql' => [
            'keys' => ['id' => 'primary', 'pid' => 'index'],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['type', 'start'],
            'headerFields' => ['title', 'meeting_point', 'duration'],
            'panelLayout' => 'filter;search,limit',
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
            'slots' => ['href' => 'table=tl_tt_slot', 'icon' => 'children.svg'],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => ['href' => 'act=show', 'icon' => 'show.svg'],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{title_legend},title,type',
        'season' => '{title_legend},title,type;{season_legend},start,stop,recurring,priority',
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
        'title' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'inputType' => 'select',
            'options' => ['default', 'season'],
            'reference' => &$GLOBALS['TL_LANG']['tl_tt_schedule']['types'],
            'filter' => true,
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default 'default'",
        ],
        'start' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'mandatory' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'stop' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'mandatory' => true, 'tl_class' => 'w50 wizard'],
            'save_callback' => [['Derkosmonaut\\TourTimesBundle\\Dca\\DateRangeCallback', 'validateStop']],
            'sql' => 'int(10) unsigned NULL',
        ],
        'recurring' => [
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'priority' => [
            'inputType' => 'text',
            'default' => 0,
            'eval' => ['rgxp' => 'digit', 'maxlength' => 3, 'tl_class' => 'w50'],
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
    ],
];
