<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Holiday;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Gesetzliche Feiertage in Deutschland, bundesweit und je Bundesland.
 *
 * Regionen: "DE" = nur bundesweite Feiertage, sonst das Kürzel des Bundeslandes (BW, BY, ...).
 * Regional unterschiedlich geltende Feiertage (z. B. Mariä Himmelfahrt in Bayern, Augsburger
 * Friedensfest) sind bewusst nicht enthalten; sie lassen sich als Ausnahme anlegen.
 */
final class GermanHolidays
{
    public const REGIONS = ['DE', 'BW', 'BY', 'BE', 'BB', 'HB', 'HH', 'HE', 'MV', 'NI', 'NW', 'RP', 'SL', 'SN', 'ST', 'SH', 'TH'];

    /**
     * @return array<string, string> Datum ("Y-m-d") => Bezeichnung, nach Datum sortiert
     */
    public static function forYear(int $year, string $region): array
    {
        $region = strtoupper($region);

        if (!\in_array($region, self::REGIONS, true)) {
            return [];
        }

        $easter = self::easter($year);
        $days = [
            $year.'-01-01' => 'Neujahr',
            self::offset($easter, -2) => 'Karfreitag',
            self::offset($easter, 1) => 'Ostermontag',
            $year.'-05-01' => 'Tag der Arbeit',
            self::offset($easter, 39) => 'Christi Himmelfahrt',
            self::offset($easter, 50) => 'Pfingstmontag',
            $year.'-10-03' => 'Tag der Deutschen Einheit',
            $year.'-12-25' => '1. Weihnachtstag',
            $year.'-12-26' => '2. Weihnachtstag',
        ];

        if (\in_array($region, ['BW', 'BY', 'ST'], true)) {
            $days[$year.'-01-06'] = 'Heilige Drei Könige';
        }

        if (\in_array($region, ['BE', 'MV'], true)) {
            $days[$year.'-03-08'] = 'Internationaler Frauentag';
        }

        if ('BB' === $region) {
            $days[self::offset($easter, 0)] = 'Ostersonntag';
            $days[self::offset($easter, 49)] = 'Pfingstsonntag';
        }

        if (\in_array($region, ['BW', 'BY', 'HE', 'NW', 'RP', 'SL'], true)) {
            $days[self::offset($easter, 60)] = 'Fronleichnam';
        }

        if ('SL' === $region) {
            $days[$year.'-08-15'] = 'Mariä Himmelfahrt';
        }

        if ('TH' === $region) {
            $days[$year.'-09-20'] = 'Weltkindertag';
        }

        if (\in_array($region, ['BB', 'HB', 'HH', 'MV', 'NI', 'SN', 'ST', 'SH', 'TH'], true)) {
            $days[$year.'-10-31'] = 'Reformationstag';
        }

        if (\in_array($region, ['BW', 'BY', 'NW', 'RP', 'SL'], true)) {
            $days[$year.'-11-01'] = 'Allerheiligen';
        }

        if ('SN' === $region) {
            $days[self::bussUndBettag($year)] = 'Buß- und Bettag';
        }

        ksort($days);

        return $days;
    }

    /**
     * Feiertage mehrerer Jahre, nur die Daten.
     *
     * @return list<string>
     */
    public static function datesForYears(int $fromYear, int $toYear, string $region): array
    {
        $dates = [];

        for ($year = $fromYear; $year <= $toYear; ++$year) {
            array_push($dates, ...array_keys(self::forYear($year, $region)));
        }

        return $dates;
    }

    /**
     * Ostersonntag nach der Gaußschen Osterformel (gregorianischer Kalender).
     */
    private static function easter(int $year): DateTimeImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return new DateTimeImmutable(\sprintf('%04d-%02d-%02d', $year, $month, $day), new DateTimeZone('UTC'));
    }

    private static function offset(DateTimeImmutable $easter, int $days): string
    {
        return $easter->modify(\sprintf('%+d day', $days))->format('Y-m-d');
    }

    /**
     * Mittwoch vor dem 23. November.
     */
    private static function bussUndBettag(int $year): string
    {
        $date = new DateTimeImmutable($year.'-11-22', new DateTimeZone('UTC'));

        while (3 !== (int) $date->format('N')) {
            $date = $date->modify('-1 day');
        }

        return $date->format('Y-m-d');
    }
}
