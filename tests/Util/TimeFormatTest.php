<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Tests\Util;

use Derkosmonaut\TourTimesBundle\Util\TimeFormat;
use PHPUnit\Framework\TestCase;

final class TimeFormatTest extends TestCase
{
    public function testNormalize(): void
    {
        self::assertSame('09:00', TimeFormat::normalize('9:00'));
        self::assertSame('10:00', TimeFormat::normalize('10'));
        self::assertSame('10:30', TimeFormat::normalize('10.30'));
        self::assertSame('24:00', TimeFormat::normalize('24:00', true));
        self::assertNull(TimeFormat::normalize('24:00'));
        self::assertNull(TimeFormat::normalize('10:60'));
        self::assertNull(TimeFormat::normalize('abc'));
        self::assertNull(TimeFormat::normalize(''));
    }
}
