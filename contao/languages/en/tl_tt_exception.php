<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_tt_exception']['title'] = ['Name', 'Only for the back end list, e.g. "Cleaning".'];
$GLOBALS['TL_LANG']['tl_tt_exception']['type'] = ['Type', 'What happens on these days.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['start'] = ['Date', 'First day of the exception.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['stop'] = ['Until', 'Optional. Empty = a single day.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['recurring'] = ['Repeat every year', 'E.g. for Christmas Eve.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['scope'] = ['Applies to', 'All dates of the offer or selected time slots only.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['slots'] = ['Time slots', 'The affected time slots.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['starts'] = ['Start', 'New time or time of the extra date (HH:MM).'];
$GLOBALS['TL_LANG']['tl_tt_exception']['ends'] = ['End', 'Optional. Empty = same length as the time slot or duration of the offer.'];
$GLOBALS['TL_LANG']['tl_tt_exception']['note'] = ['Note', 'Shown with the date in the front end, e.g. "Meet at the side entrance".'];

$GLOBALS['TL_LANG']['tl_tt_exception']['types'] = [
    'cancel' => 'Cancelled',
    'shift' => 'Different time',
    'note' => 'Takes place, with a note',
    'extra' => 'Extra date',
];
$GLOBALS['TL_LANG']['tl_tt_exception']['scopes'] = ['offer' => 'All dates of the offer', 'slots' => 'Selected time slots'];

$GLOBALS['TL_LANG']['tl_tt_exception']['title_legend'] = 'Exception';
$GLOBALS['TL_LANG']['tl_tt_exception']['date_legend'] = 'Date';
$GLOBALS['TL_LANG']['tl_tt_exception']['scope_legend'] = 'Affected dates';
$GLOBALS['TL_LANG']['tl_tt_exception']['time_legend'] = 'Time';
$GLOBALS['TL_LANG']['tl_tt_exception']['note_legend'] = 'Note';

$GLOBALS['TL_LANG']['tl_tt_exception']['new'] = ['New exception', 'Create a new exception'];
$GLOBALS['TL_LANG']['tl_tt_exception']['edit'] = ['Edit exception', 'Edit exception ID %s'];
$GLOBALS['TL_LANG']['tl_tt_exception']['copy'] = ['Duplicate exception', 'Duplicate exception ID %s'];
$GLOBALS['TL_LANG']['tl_tt_exception']['delete'] = ['Delete exception', 'Delete exception ID %s'];
$GLOBALS['TL_LANG']['tl_tt_exception']['show'] = ['Details', 'Details of exception ID %s'];
