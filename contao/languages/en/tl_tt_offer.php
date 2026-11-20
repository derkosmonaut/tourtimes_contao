<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_tt_offer']['title'] = ['Title', 'Name of the offer, e.g. "Cathedral tour".'];
$GLOBALS['TL_LANG']['tl_tt_offer']['alias'] = ['Alias', 'URL part of the detail page. Generated from the title if left empty.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['teaser'] = ['Teaser', 'Shown in the list.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['description'] = ['Description', 'Full text for the detail view.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['singleSRC'] = ['Image', 'Optional image of the offer.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['meeting_point'] = ['Meeting point', 'E.g. "Main entrance".'];
$GLOBALS['TL_LANG']['tl_tt_offer']['duration'] = ['Duration in minutes', 'Used for all dates without an end time. 0 = no end.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['price'] = ['Price', 'Free text, e.g. "€12, reduced €8".'];
$GLOBALS['TL_LANG']['tl_tt_offer']['language'] = ['Language', 'E.g. "German". Add differences of single dates as a note on the time slot.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['ticket_url'] = ['Ticket link', 'Address of the ticket shop.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['ticket_label'] = ['Link text', 'Empty = "Tickets".'];
$GLOBALS['TL_LANG']['tl_tt_offer']['published'] = ['Published', 'Only published offers appear in the front end.'];

$GLOBALS['TL_LANG']['tl_tt_offer']['date_from'] = ['Dates from', 'Optional. No dates take place before.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['date_until'] = ['Dates until', 'Optional. No dates take place afterwards and the offer disappears from the list.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['max_dates'] = ['Ends after dates', 'Optional, e.g. 5. Counted from "Dates from", every date counts (including cancelled and extra dates). 0 = unlimited.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['period_legend'] = 'Period';
$GLOBALS['TL_LANG']['tl_tt_offer']['title_legend'] = 'Offer';
$GLOBALS['TL_LANG']['tl_tt_offer']['text_legend'] = 'Texts';
$GLOBALS['TL_LANG']['tl_tt_offer']['image_legend'] = 'Image';
$GLOBALS['TL_LANG']['tl_tt_offer']['details_legend'] = 'Details';
$GLOBALS['TL_LANG']['tl_tt_offer']['ticket_legend'] = 'Tickets';
$GLOBALS['TL_LANG']['tl_tt_offer']['publish_legend'] = 'Publishing';

$GLOBALS['TL_LANG']['tl_tt_offer']['new'] = ['New offer', 'Create a new offer'];
$GLOBALS['TL_LANG']['tl_tt_offer']['edit'] = ['Edit offer', 'Edit offer ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['schedules'] = ['Schedules', 'Default and seasonal schedules of offer ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['exceptions'] = ['Exceptions', 'Exceptions of offer ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['cancellations'] = ['Cancellations', 'Short-notice cancellations of offer ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['copy'] = ['Duplicate offer', 'Duplicate offer ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['toggle'] = ['Publish', 'Publish/unpublish offer ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['delete'] = ['Delete offer', 'Delete offer ID %s'];
$GLOBALS['TL_LANG']['tl_tt_offer']['show'] = ['Details', 'Details of offer ID %s'];
