<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_module']['tourtimes_offers'] = ['Offers', 'Selection and order of the offers. Without a selection all published offers appear.'];
$GLOBALS['TL_LANG']['tl_module']['tourtimes_upcoming'] = ['Number of upcoming dates', 'How many upcoming dates are shown per offer. 0 = none.'];
$GLOBALS['TL_LANG']['tl_module']['tourtimes_offset'] = ['Offset in minutes', 'A date is hidden this many minutes before it starts, e.g. 10 for the info screen at the ticket desk.'];
$GLOBALS['TL_LANG']['tl_module']['tourtimes_show_cancelled'] = ['Show cancelled dates', 'Cancelled dates appear struck through with a note. Deselected they are hidden.'];
$GLOBALS['TL_LANG']['tl_module']['tourtimes_empty'] = ['Text without dates', 'Shown when there are no more dates today. Empty = default text.'];

$GLOBALS['TL_LANG']['tl_module']['tourtimes_legend'] = 'Tours and dates';
$GLOBALS['TL_LANG']['tl_module']['tourtimes_reader_legend'] = 'Upcoming dates of the offer';
$GLOBALS['TL_LANG']['tl_module']['tourtimes_hide_in_reader'] = ['Hide in detail view', 'The module disappears as soon as an offer is selected. This lets list and detail view share one page: add both modules and set this page as redirect page.'];
$GLOBALS['TL_LANG']['tl_module']['tourtimes_show_next'] = ['Show next day', 'When there is nothing left today, the dates of the next day with dates appear below the message (e.g. "Tomorrow").'];
$GLOBALS['TL_LANG']['tl_module']['tourtimes_jsonld'] = ['Output structured data', 'The displayed dates as schema.org events (JSON-LD) for search engines, cancelled ones marked as cancelled.'];
