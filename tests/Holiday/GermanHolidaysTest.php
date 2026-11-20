<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Tests\Holiday;

use Derkosmonaut\TourTimesBundle\Holiday\GermanHolidays;
use PHPUnit\Framework\TestCase;

final class GermanHolidaysTest extends TestCase
{
    public function testMovableHolidaysDependOnEaster(): void
    {
        // Ostersonntag 2026: 5. April
        $holidays = GermanHolidays::forYear(2026, 'DE');

        self::assertSame('Karfreitag', $holidays['2026-04-03']);
        self::assertSame('Ostermontag', $holidays['2026-04-06']);
        self::assertSame('Christi Himmelfahrt', $holidays['2026-05-14']);
        self::assertSame('Pfingstmontag', $holidays['2026-05-25']);

        // Ostersonntag 2027: 28. März
        self::assertArrayHasKey('2027-03-26', GermanHolidays::forYear(2027, 'DE'));
    }

    public function testNationwideHolidays(): void
    {
        $dates = array_keys(GermanHolidays::forYear(2026, 'DE'));

        self::assertSame(
            ['2026-01-01', '2026-04-03', '2026-04-06', '2026-05-01', '2026-05-14', '2026-05-25', '2026-10-03', '2026-12-25', '2026-12-26'],
            $dates,
        );
    }

    public function testHamburgHasReformationDayButNoCorpusChristi(): void
    {
        $holidays = GermanHolidays::forYear(2026, 'HH');

        self::assertArrayHasKey('2026-10-31', $holidays);
        self::assertArrayNotHasKey('2026-06-04', $holidays);
        self::assertArrayNotHasKey('2026-11-01', $holidays);
    }

    public function testRegionalHolidays(): void
    {
        self::assertArrayHasKey('2026-06-04', GermanHolidays::forYear(2026, 'BY')); // Fronleichnam
        self::assertArrayHasKey('2026-01-06', GermanHolidays::forYear(2026, 'BW')); // Heilige Drei Könige
        self::assertArrayHasKey('2026-03-08', GermanHolidays::forYear(2026, 'BE')); // Frauentag
        self::assertArrayHasKey('2026-09-20', GermanHolidays::forYear(2026, 'TH')); // Weltkindertag
        self::assertArrayHasKey('2026-11-18', GermanHolidays::forYear(2026, 'SN')); // Buß- und Bettag
        self::assertArrayHasKey('2026-04-05', GermanHolidays::forYear(2026, 'BB')); // Ostersonntag
    }

    public function testUnknownRegionHasNoHolidays(): void
    {
        self::assertSame([], GermanHolidays::forYear(2026, 'XX'));
    }

    public function testDatesForYears(): void
    {
        $dates = GermanHolidays::datesForYears(2026, 2027, 'DE');

        self::assertContains('2026-12-25', $dates);
        self::assertContains('2027-12-25', $dates);
    }
}
