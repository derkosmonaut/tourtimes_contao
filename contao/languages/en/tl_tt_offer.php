<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_tt_offer']['title'] = ['Title', 'Name of the offer, e.g. "Cathedral tour".'];
$GLOBALS['TL_LANG']['tl_tt_offer']['alias'] = ['Alias', 'URL part of the detail page. Generated from the title if left empty.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['teaser'] = ['Teaser', 'Shown in the list.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['description'] = ['Description', 'Full text for the detail view.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['singleSRC'] = ['Image', 'Optional image of the offer.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['time_mode'] = ['Type of dates', 'Fixed start time: the date starts at the given time and then disappears. Time window: start any time between start and end (e.g. audio guide 10–12), the date stays visible until the end. Time windows need an end on the time slot.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['time_modes'] = ['start' => 'Fixed start time (e.g. guided tour)', 'window' => 'Time window, start any time (e.g. audio guide)'];
$GLOBALS['TL_LANG']['tl_tt_offer']['meeting_point'] = ['Meeting point', 'E.g. "Main entrance".'];
$GLOBALS['TL_LANG']['tl_tt_offer']['duration'] = ['Duration in minutes', 'Fixed start time: used for all dates without an end. Time window: information for visitors only (e.g. "approx. 60 min"), the end comes from the time slot. 0 = not shown.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['price'] = ['Price', 'Free text, e.g. "€12, reduced €8".'];
$GLOBALS['TL_LANG']['tl_tt_offer']['language'] = ['Language', 'E.g. "German". Add differences of single dates as a note on the time slot.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['ticket_url'] = ['Ticket link', 'Address of the ticket shop.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['ticket_label'] = ['Link text', 'Empty = "Tickets".'];
$GLOBALS['TL_LANG']['tl_tt_offer']['published'] = ['Published', 'Only published offers appear in the front end.'];

$GLOBALS['TL_LANG']['tl_tt_offer']['date_from'] = ['Dates from', 'Optional. No dates take place before.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['date_until'] = ['Dates until', 'Optional. No dates take place afterwards and the offer disappears from the list.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['max_dates'] = ['Ends after dates', 'Optional, e.g. 5. Counted from "Dates from", every date counts (including cancelled and extra dates). 0 = unlimited.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['holiday_region'] = ['No dates on public holidays', 'Regular dates are dropped on public holidays of this state. Empty = dates also on holidays. Extra dates and exceptions (different time, note) for the day take precedence.'];
$GLOBALS['TL_LANG']['tl_tt_offer']['regions'] = [
    'DE' => 'Nationwide holidays only',
    'BW' => 'Baden-Württemberg', 'BY' => 'Bavaria', 'BE' => 'Berlin', 'BB' => 'Brandenburg',
    'HB' => 'Bremen', 'HH' => 'Hamburg', 'HE' => 'Hesse', 'MV' => 'Mecklenburg-Vorpommern',
    'NI' => 'Lower Saxony', 'NW' => 'North Rhine-Westphalia', 'RP' => 'Rhineland-Palatinate', 'SL' => 'Saarland',
    'SN' => 'Saxony', 'ST' => 'Saxony-Anhalt', 'SH' => 'Schleswig-Holstein', 'TH' => 'Thuringia',
];
$GLOBALS['TL_LANG']['tl_tt_offer']['period_legend'] = 'Period';
$GLOBALS['TL_LANG']['tl_tt_offer']['tourtimes_preview'] = ['Preview: upcoming dates', ''];
$GLOBALS['TL_LANG']['tl_tt_offer']['preview_legend'] = 'Preview';
$GLOBALS['TL_LANG']['tl_tt_offer']['warnings'] = [
    'no_plan' => 'No schedule and no extra date has been added yet. No dates appear in the front end.',
    'default_empty' => 'The default schedule contains no valid time slot.',
    'season_empty' => 'The seasonal schedule "%s" has no time slot. No dates take place during this season.',
    'ended' => 'The offer has ended (period over or last date started) and no longer appears in the offer list.',
    'not_started' => 'The offer only starts on %s.',
    'count_not_reached' => 'The %s dates are not reached within the period. Only the dates until "Dates until" apply.',
    'cancellation_active' => 'Short-notice cancellation active: %s',
    'cancellation_planned' => 'Short-notice cancellation planned: %s',
    'window_without_end' => '%s time slot(s) without an end: in time window mode a date without an end is treated like a fixed start time.',
    'no_upcoming' => 'There is no date in the next %s days.',
];
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
