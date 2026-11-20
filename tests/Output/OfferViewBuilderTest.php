<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Tests\Output;

use DateTimeImmutable;
use DateTimeZone;
use Derkosmonaut\TourTimesBundle\Output\OfferViewBuilder;
use Derkosmonaut\TourTimesBundle\Repository\OfferRepository;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Cancellation;
use Derkosmonaut\TourTimesBundle\Resolver\Model\OfferPlan;
use Derkosmonaut\TourTimesBundle\Resolver\Model\TimeSlot;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;
use PHPUnit\Framework\TestCase;

/**
 * Tagesliste über zwei Angebote am Montag, 16.11.2026:
 *   Domführung (1):  10:00, 14:00 (14:00 abgesagt)
 *   Turmführung (2): 11:00, 15:00
 */
final class OfferViewBuilderTest extends TestCase
{
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Berlin');
    }

    private function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable($time, $this->timezone);
    }

    private function builder(): OfferViewBuilder
    {
        $plans = [
            1 => new OfferPlan(1, [new TimeSlot(1, [1], '10:00'), new TimeSlot(2, [1], '14:00')], [], [], [
                new Cancellation($this->at('2026-11-16 13:00'), $this->at('2026-11-16 18:00'), true, 'Krankheitsbedingt', [2]),
            ], 60),
            2 => new OfferPlan(2, [new TimeSlot(3, [1], '11:00'), new TimeSlot(4, [1], '15:00')], [], [], [], 45),
        ];

        // Repository ohne Datenbank: liefert die Pläne oben
        $repository = new class($plans) extends OfferRepository {
            /** @param array<int, OfferPlan> $plans */
            public function __construct(private readonly array $plans)
            {
            }

            public function loadPlan(int $offerId, int $duration, DateTimeZone $timezone): OfferPlan
            {
                return $this->plans[$offerId];
            }
        };

        return new OfferViewBuilder($repository, new OccurrenceResolver());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function offers(): array
    {
        $offer = ['teaser' => '', 'description' => '', 'image' => null, 'meetingPoint' => '', 'price' => '', 'language' => '', 'ticketUrl' => '', 'ticketLabel' => '', 'alias' => ''];

        return [
            ['id' => 1, 'title' => 'Domführung', 'duration' => 60, ...$offer],
            ['id' => 2, 'title' => 'Turmführung', 'duration' => 45, ...$offer],
        ];
    }

    /**
     * @param list<array<string, mixed>> $entries
     *
     * @return list<string>
     */
    private function times(array $entries): array
    {
        return array_map(static fn (array $e): string => $e['start']->format('H:i').' '.$e['offer']['title'], $entries);
    }

    public function testTodaySortedAcrossOffers(): void
    {
        $result = $this->builder()->buildToday($this->offers(), $this->at('2026-11-16 08:00'), 0, true);

        self::assertSame(['10:00 Domführung', '11:00 Turmführung', '14:00 Domführung', '15:00 Turmführung'], $this->times($result['entries']));
        self::assertTrue($result['entries'][2]['cancelled']);
        self::assertSame('2026-11-16 10:00', $result['nextChange']->format('Y-m-d H:i'));
    }

    public function testStartedDatesDisappear(): void
    {
        $result = $this->builder()->buildToday($this->offers(), $this->at('2026-11-16 10:00'), 0, true);

        self::assertSame(['11:00 Turmführung', '14:00 Domführung', '15:00 Turmführung'], $this->times($result['entries']));
    }

    public function testOffsetHidesEarlier(): void
    {
        $result = $this->builder()->buildToday($this->offers(), $this->at('2026-11-16 10:50'), 10, true);

        self::assertSame(['14:00 Domführung', '15:00 Turmführung'], $this->times($result['entries']));
        self::assertSame('2026-11-16 13:50', $result['entries'][0]['hideAt']->format('Y-m-d H:i'));
        self::assertSame('2026-11-16 13:50', $result['nextChange']->format('Y-m-d H:i'));
    }

    public function testCancelledCanBeHidden(): void
    {
        $result = $this->builder()->buildToday($this->offers(), $this->at('2026-11-16 08:00'), 0, false);

        self::assertSame(['10:00 Domführung', '11:00 Turmführung', '15:00 Turmführung'], $this->times($result['entries']));
    }

    public function testAfterLastDateUntilNextDay(): void
    {
        $result = $this->builder()->buildToday($this->offers(), $this->at('2026-11-16 15:30'), 0, true);

        self::assertSame([], $result['entries']);
        self::assertSame('2026-11-17 00:00', $result['nextChange']->format('Y-m-d H:i'));
    }

    public function testMaxAgeIsClamped(): void
    {
        $now = $this->at('2026-11-16 08:00');

        self::assertSame(OfferViewBuilder::MIN_AGE, OfferViewBuilder::maxAge($now, $now->modify('+5 seconds')));
        self::assertSame(600, OfferViewBuilder::maxAge($now, $now->modify('+10 minutes')));
        self::assertSame(OfferViewBuilder::MAX_AGE, OfferViewBuilder::maxAge($now, $now->modify('+5 hours')));
    }

    public function testOffersWithRegularTimesAndUpcoming(): void
    {
        $views = $this->builder()->buildOffers($this->offers(), $this->at('2026-11-16 12:00'), 2, false, [1 => 'Mo']);

        self::assertSame('Mo', $views[0]['regular'][0]['days']);
        self::assertSame(
            ['2026-11-23 10:00', '2026-11-23 14:00'],
            array_map(static fn (array $o): string => $o['start']->format('Y-m-d H:i'), $views[0]['upcoming']),
            'der abgesagte 14:00-Termin am 16.11. fällt weg',
        );
        self::assertSame('2026-11-16 15:00', OfferViewBuilder::nextUpcomingChange($views, $this->at('2026-11-16 12:00'))->format('Y-m-d H:i'));
    }

    public function testEndedOffersAreSkippedInList(): void
    {
        $plan = new OfferPlan(5, [new TimeSlot(9, [1], '10:00')], [], [], [], 60, '2026-11-02', null, 2);
        $repository = new class($plan) extends OfferRepository {
            public function __construct(private readonly OfferPlan $plan)
            {
            }

            public function loadPlan(int $offerId, int $duration, DateTimeZone $timezone): OfferPlan
            {
                return $this->plan;
            }
        };
        $builder = new OfferViewBuilder($repository, new OccurrenceResolver());
        $offer = [['id' => 5, 'title' => 'Herbstführung', 'duration' => 60, 'alias' => '']];

        self::assertSame([], $builder->buildOffers($offer, $this->at('2026-11-16 12:00'), 3, false, []));

        $views = $builder->buildOffers($offer, $this->at('2026-11-16 12:00'), 3, false, [], false);
        self::assertTrue($views[0]['ended']);
        self::assertSame('2026-11-09', $views[0]['validUntil']?->format('Y-m-d'), 'Tag des letzten Termins');
    }
}
