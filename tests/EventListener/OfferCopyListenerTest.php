<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Tests\EventListener;

use Derkosmonaut\TourTimesBundle\EventListener\OfferCopyListener;
use PHPUnit\Framework\TestCase;

final class OfferCopyListenerTest extends TestCase
{
    public function testMapsByPosition(): void
    {
        self::assertSame([3 => 13, 5 => 15, 9 => 19], OfferCopyListener::mapIds([3, 5, 9], [13, 15, 19]));
        self::assertSame([], OfferCopyListener::mapIds([3, 5], [13]), 'unterschiedliche Anzahl: nichts zuordnen');
    }

    public function testRemap(): void
    {
        $map = [3 => 13, 5 => 15];

        self::assertSame(serialize(['13', '15']), OfferCopyListener::remap(serialize(['3', '5']), $map));
        self::assertNull(OfferCopyListener::remap(serialize(['7']), $map), 'unbekannte ID bleibt');
        self::assertNull(OfferCopyListener::remap('', $map));
    }
}
