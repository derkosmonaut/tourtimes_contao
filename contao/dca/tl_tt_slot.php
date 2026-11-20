<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

/*
 * Zeitslot eines Plans: ein Termin, der an mehreren Wochentagen stattfinden kann
 * (ISO: 1 = Montag ... 7 = Sonntag). Ohne Ende gilt die Dauer des Angebots.
 */
// Schnellauswahl für die Wochentage (Mo–Fr, Wochenende, alle)
$GLOBALS['TL_JAVASCRIPT']['tourtimes_backend'] = 'bundles/derkosmonauttourtimes/js/backend.js';

$GLOBALS['TL_DCA']['tl_tt_slot'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_tt_schedule',
        'sql' => [
            'keys' => ['id' => 'primary', 'pid' => 'index'],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['starts'],
            'headerFields' => ['title', 'type'],
            'panelLayout' => 'limit',
        ],
        'label' => [
            'fields' => ['weekdays', 'starts', 'ends', 'note'],
            'label_callback' => static function (array $row): string {
                $names = $GLOBALS['TL_LANG']['tl_tt_slot']['weekday_names'] ?? [];
                $days = array_map(
                    static fn ($day): string => mb_substr((string) ($names[(int) $day] ?? $day), 0, 2),
                    (array) Contao\StringUtil::deserialize($row['weekdays'], true),
                );
                $time = $row['starts'].('' !== (string) $row['ends'] ? ' – '.$row['ends'] : '');
                $note = '' !== (string) $row['note'] ? ' <span class="tl_gray">('.Contao\StringUtil::specialchars($row['note']).')</span>' : '';

                return implode(', ', $days).': '.$time.$note;
            },
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
        'default' => '{slot_legend},weekdays,starts,ends,note',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_tt_schedule.title',
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'weekdays' => [
            'inputType' => 'checkbox',
            'options' => [1, 2, 3, 4, 5, 6, 7],
            'reference' => &$GLOBALS['TL_LANG']['tl_tt_slot']['weekday_names'],
            'eval' => ['multiple' => true, 'mandatory' => true, 'tl_class' => 'clr'],
            'sql' => 'blob NULL',
        ],
        // Uhrzeiten als "HH:MM", geprüft und vereinheitlicht durch TimeInputCallback
        'starts' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 5, 'placeholder' => '10:00', 'tl_class' => 'w50'],
            'save_callback' => [['Derkosmonaut\\TourTimesBundle\\Dca\\TimeInputCallback', 'validateOpens']],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        // leer = Dauer des Angebots
        'ends' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 5, 'placeholder' => '11:00', 'tl_class' => 'w50'],
            'save_callback' => [['Derkosmonaut\\TourTimesBundle\\Dca\\TimeInputCallback', 'validateCloses']],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        // fester Hinweis, z. B. "auf Englisch"
        'note' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'long clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
