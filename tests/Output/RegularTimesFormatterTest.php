<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Tests\Output;

use Derkosmonaut\TourTimesBundle\Output\RegularTimesFormatter;
use Derkosmonaut\TourTimesBundle\Resolver\Model\TimeSlot;
use PHPUnit\Framework\TestCase;

final class RegularTimesFormatterTest extends TestCase
{
    private const NAMES = [1 => 'Mo', 2 => 'Di', 3 => 'Mi', 4 => 'Do', 5 => 'Fr', 6 => 'Sa', 7 => 'So'];

    public function testFormatDays(): void
    {
        self::assertSame('Mo–Mi', RegularTimesFormatter::formatDays([1, 2, 3], self::NAMES));
        self::assertSame('Mo, Di, Do', RegularTimesFormatter::formatDays([1, 2, 4], self::NAMES));
        self::assertSame('Mo–Mi, Fr–So', RegularTimesFormatter::formatDays([1, 2, 3, 5, 6, 7], self::NAMES));
        self::assertSame('Mo–So', RegularTimesFormatter::formatDays([1, 2, 3, 4, 5, 6, 7], self::NAMES));
        self::assertSame('Sa', RegularTimesFormatter::formatDays([6], self::NAMES));
    }

    public function testGroupsSameTimeAcrossSlots(): void
    {
        $rows = RegularTimesFormatter::group([
            new TimeSlot(2, [6], '14:00'),
            new TimeSlot(1, [1, 2], '10:00', '11:00'),
            new TimeSlot(3, [3], '10:00', '11:00'),
        ], self::NAMES);

        self::assertCount(2, $rows);
        self::assertSame('Mo–Mi', $rows[0]['days']);
        self::assertSame('10:00', $rows[0]['starts']);
        self::assertSame('Sa', $rows[1]['days']);
        self::assertNull($rows[1]['ends']);
    }

    public function testDifferentNotesStaySeparate(): void
    {
        $rows = RegularTimesFormatter::group([
            new TimeSlot(1, [1], '15:00'),
            new TimeSlot(2, [2], '15:00', null, 'auf Englisch'),
        ], self::NAMES);

        self::assertCount(2, $rows);
    }
}
