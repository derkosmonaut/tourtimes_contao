<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Tests\Diagnostics;

use DateTimeImmutable;
use DateTimeZone;
use Derkosmonaut\TourTimesBundle\Diagnostics\OfferDiagnostics;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Cancellation;
use Derkosmonaut\TourTimesBundle\Resolver\Model\OfferPlan;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Season;
use Derkosmonaut\TourTimesBundle\Resolver\Model\TimeSlot;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;
use PHPUnit\Framework\TestCase;

final class OfferDiagnosticsTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function keys(OfferPlan $plan, string $now = '2026-11-16 08:00'): array
    {
        $result = (new OfferDiagnostics(new OccurrenceResolver()))->analyze($plan, new DateTimeImmutable($now, new DateTimeZone('Europe/Berlin')));

        return array_column($result, 'key');
    }

    public function testNoPlan(): void
    {
        self::assertSame(['no_plan'], $this->keys(new OfferPlan(1, null)));
    }

    public function testHealthyPlanHasNoWarnings(): void
    {
        self::assertSame([], $this->keys(new OfferPlan(1, [new TimeSlot(1, [1], '10:00')])));
    }

    public function testEmptyDefaultAndSeason(): void
    {
        $plan = new OfferPlan(1, [], [new Season('2026-06-01', '2026-08-31', true, 0, [], 'Sommer')]);

        self::assertSame(['default_empty', 'season_empty', 'no_upcoming'], $this->keys($plan));
    }

    public function testPeriodWarnings(): void
    {
        $slots = [new TimeSlot(1, [1], '10:00')];

        self::assertSame(['ended'], $this->keys(new OfferPlan(1, $slots, [], [], [], 60, '2026-06-01', '2026-06-30')));
        self::assertSame(['not_started'], $this->keys(new OfferPlan(1, $slots, [], [], [], 60, '2027-06-01')));
        self::assertSame(['count_not_reached'], $this->keys(new OfferPlan(1, $slots, [], [], [], 60, '2026-11-16', '2026-11-30', 10)));
    }

    public function testCancellations(): void
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $plan = new OfferPlan(1, [new TimeSlot(1, [1], '10:00')], [], [], [
            new Cancellation(new DateTimeImmutable('2026-11-16 07:00', $tz), null, true, 'Bauarbeiten'),
            new Cancellation(new DateTimeImmutable('2026-12-01 00:00', $tz), new DateTimeImmutable('2026-12-02 00:00', $tz), true, 'Betriebsausflug'),
            new Cancellation(new DateTimeImmutable('2026-11-01 00:00', $tz), null, false, 'aufgehoben'),
        ]);

        self::assertSame(['cancellation_active', 'cancellation_planned', 'no_upcoming'], $this->keys($plan));
    }

    public function testWindowWithoutEnd(): void
    {
        $plan = new OfferPlan(1, [new TimeSlot(1, [1], '10:00', '12:00'), new TimeSlot(2, [3], '14:00')], window: true);

        self::assertSame(['window_without_end'], $this->keys($plan));
        self::assertSame([], $this->keys(new OfferPlan(1, [new TimeSlot(2, [3], '14:00')])), 'feste Startzeit braucht kein Ende');
    }
}
