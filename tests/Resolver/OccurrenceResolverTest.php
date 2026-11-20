<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Tests\Resolver;

use DateTimeImmutable;
use DateTimeZone;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Cancellation;
use Derkosmonaut\TourTimesBundle\Resolver\Model\DayException;
use Derkosmonaut\TourTimesBundle\Resolver\Model\OfferPlan;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Occurrence;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Season;
use Derkosmonaut\TourTimesBundle\Resolver\Model\TimeSlot;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;
use PHPUnit\Framework\TestCase;

/**
 * Beispielangebot: Domführung, Dauer 90 Minuten.
 *
 * Standard:  Slot 1 Mo/Di/Mi 10:00–11:00, Slot 2 Sa 14:00 (ohne Ende = Dauer)
 * Sommer:    1.4.–31.10. jährlich, Slot 3 täglich 10:00, Slot 4 täglich 15:00 „auf Englisch“
 * Ausnahmen: 23.11.2026 Slot 1 fällt aus, 24.11. Slot 1 auf 11:30 verschoben,
 *            25.11. Hinweis für alle, 26.11. Zusatztermin 16:00, 24.12. jährlich alles aus
 * Absage:    28.11.2026 13:00–18:00 (ganzes Angebot)
 *
 * Kalenderhilfe 2026: 16.11. Mo, 23.11. Mo, 24.11. Di, 25.11. Mi, 26.11. Do, 28.11. Sa;
 * 24.12.2025 Mi; 05.04.2027 Mo.
 */
final class OccurrenceResolverTest extends TestCase
{
    private OccurrenceResolver $resolver;
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->resolver = new OccurrenceResolver();
        $this->timezone = new DateTimeZone('Europe/Berlin');
    }

    /**
     * @param list<Cancellation>|null $cancellations null = die Standard-Absage am 28.11.
     */
    private function plan(?array $cancellations = null): OfferPlan
    {
        return new OfferPlan(
            7,
            [
                new TimeSlot(1, [1, 2, 3], '10:00', '11:00'),
                new TimeSlot(2, [6], '14:00'),
            ],
            [
                new Season('2026-04-01', '2026-10-31', true, 0, [
                    new TimeSlot(3, [1, 2, 3, 4, 5, 6, 7], '10:00'),
                    new TimeSlot(4, [1, 2, 3, 4, 5, 6, 7], '15:00', null, 'auf Englisch'),
                ], 'Sommer'),
            ],
            [
                new DayException('2026-11-23', null, false, DayException::CANCEL, [1], null, null, 'Domputz'),
                new DayException('2026-11-24', null, false, DayException::SHIFT, [1], '11:30', null, 'Später wegen Gottesdienst'),
                new DayException('2026-11-25', null, false, DayException::NOTE, [], null, null, 'Treffpunkt am Seitenportal'),
                new DayException('2026-11-26', null, false, DayException::EXTRA, [], '16:00', null, 'Sonderführung'),
                new DayException('2025-12-24', null, true, DayException::CANCEL, [], null, null, 'Heiligabend'),
            ],
            $cancellations ?? [
                new Cancellation($this->at('2026-11-28 13:00'), $this->at('2026-11-28 18:00'), true, 'Krankheitsbedingt'),
            ],
            90,
        );
    }

    private function at(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable($time, $this->timezone);
    }

    /**
     * @return list<Occurrence>
     */
    private function day(string $date, ?OfferPlan $plan = null): array
    {
        return $this->resolver->getDay($plan ?? $this->plan(), $this->at($date));
    }

    public function testRegularDayFromDefaultPlan(): void
    {
        $day = $this->day('2026-11-16');

        self::assertCount(1, $day);
        self::assertSame(Occurrence::SCHEDULED, $day[0]->state);
        self::assertSame('2026-11-16 10:00', $day[0]->start->format('Y-m-d H:i'));
        self::assertSame('2026-11-16 11:00', $day[0]->end?->format('Y-m-d H:i'));
        self::assertSame(1, $day[0]->slotId);
    }

    public function testDayWithoutSlotIsEmpty(): void
    {
        self::assertSame([], $this->day('2026-11-19'));
    }

    public function testSlotWithoutEndUsesDuration(): void
    {
        $day = $this->day('2026-11-21');

        self::assertSame('2026-11-21 15:30', $day[0]->end?->format('Y-m-d H:i'));
    }

    public function testCancelExceptionForSlot(): void
    {
        $day = $this->day('2026-11-23');

        self::assertCount(1, $day);
        self::assertTrue($day[0]->isCancelled());
        self::assertSame(['Domputz'], $day[0]->notes);
    }

    public function testShiftKeepsLengthAndOriginalStart(): void
    {
        $day = $this->day('2026-11-24');

        self::assertSame(Occurrence::CHANGED, $day[0]->state);
        self::assertSame('11:30', $day[0]->start->format('H:i'));
        self::assertSame('12:30', $day[0]->end?->format('H:i'));
        self::assertSame('10:00', $day[0]->originalStart?->format('H:i'));
        self::assertSame(['Später wegen Gottesdienst'], $day[0]->notes);
    }

    public function testNoteForWholeOffer(): void
    {
        $day = $this->day('2026-11-25');

        self::assertSame(Occurrence::SCHEDULED, $day[0]->state);
        self::assertSame(['Treffpunkt am Seitenportal'], $day[0]->notes);
    }

    public function testExtraOccurrence(): void
    {
        $day = $this->day('2026-11-26');

        self::assertCount(1, $day);
        self::assertSame(Occurrence::EXTRA, $day[0]->state);
        self::assertNull($day[0]->slotId);
        self::assertSame('16:00', $day[0]->start->format('H:i'));
        self::assertSame('17:30', $day[0]->end?->format('H:i'));
    }

    public function testSpontaneousCancellation(): void
    {
        $day = $this->day('2026-11-28');

        self::assertTrue($day[0]->isCancelled());
        self::assertSame(['Krankheitsbedingt'], $day[0]->notes);
    }

    public function testCancellationOnlyCoversStartInsidePeriod(): void
    {
        $plan = $this->plan([new Cancellation($this->at('2026-11-28 14:01'), null, true, 'Ab jetzt nicht')]);

        self::assertFalse($this->day('2026-11-28', $plan)[0]->isCancelled());
        self::assertTrue($this->day('2026-12-05', $plan)[0]->isCancelled(), 'ohne Ende bis auf Weiteres');
    }

    public function testInactiveCancellationIsIgnored(): void
    {
        $plan = $this->plan([new Cancellation($this->at('2026-11-28 13:00'), null, false)]);

        self::assertFalse($this->day('2026-11-28', $plan)[0]->isCancelled());
    }

    public function testSlotCancellationLeavesExtraAlone(): void
    {
        $plan = $this->plan([new Cancellation($this->at('2026-11-26 00:00'), $this->at('2026-11-27 00:00'), true, '', [1])]);

        self::assertFalse($this->day('2026-11-26', $plan)[0]->isCancelled());
    }

    public function testRecurringExceptionInLaterYear(): void
    {
        $day = $this->day('2025-12-24');

        self::assertTrue($day[0]->isCancelled());
        self::assertSame(['Heiligabend'], $day[0]->notes);
    }

    public function testSeasonReplacesDefaultPlan(): void
    {
        $day = $this->day('2027-04-05');

        self::assertCount(2, $day);
        self::assertSame([3, 4], [$day[0]->slotId, $day[1]->slotId]);
        self::assertSame(['auf Englisch'], $day[1]->notes);
        self::assertSame('Sommer', $this->resolver->getActiveSeason($this->plan(), $this->at('2027-04-05'))?->title);
    }

    public function testUpcomingSkipsPastAndCancelled(): void
    {
        $upcoming = $this->resolver->getUpcoming($this->plan(), $this->at('2026-11-23 09:00'), 3);

        self::assertSame(
            ['2026-11-24 11:30', '2026-11-25 10:00', '2026-11-26 16:00'],
            array_map(static fn (Occurrence $o): string => $o->start->format('Y-m-d H:i'), $upcoming),
        );
    }

    public function testUpcomingWithCancelled(): void
    {
        $upcoming = $this->resolver->getUpcoming($this->plan(), $this->at('2026-11-23 09:00'), 1, true);

        self::assertTrue($upcoming[0]->isCancelled());
    }

    public function testVisibilityWithOffset(): void
    {
        $occurrence = $this->day('2026-11-16')[0];

        self::assertTrue(OccurrenceResolver::isVisible($occurrence, $this->at('2026-11-16 09:49'), 10));
        self::assertFalse(OccurrenceResolver::isVisible($occurrence, $this->at('2026-11-16 09:50'), 10));
        self::assertTrue(OccurrenceResolver::isVisible($occurrence, $this->at('2026-11-16 09:59')));
        self::assertFalse(OccurrenceResolver::isVisible($occurrence, $this->at('2026-11-16 10:00')));
    }

    public function testPlanWithoutSlotsHasNoOccurrences(): void
    {
        $plan = new OfferPlan(1, null);

        self::assertSame([], $this->resolver->getDay($plan, $this->at('2026-11-16')));
        self::assertSame([], $this->resolver->getUpcoming($plan, $this->at('2026-11-16'), 5));
    }

    public function testRangeCoversSeveralDays(): void
    {
        $range = $this->resolver->getRange($this->plan(), $this->at('2026-11-16 15:00'), 7);

        self::assertSame(
            ['2026-11-16', '2026-11-17', '2026-11-18', '2026-11-21'],
            array_map(static fn (Occurrence $o): string => $o->start->format('Y-m-d'), $range),
        );
    }

    /**
     * Sommerführung: montags 10:00, ab 1.6.2026 (Mo), endet nach 5 Terminen.
     * Termine: 1.6., 8.6., 15.6. (abgesagt, zählt mit), 22.6., 29.6.
     */
    private function summerPlan(int $maxDates = 5, ?string $until = null): OfferPlan
    {
        return new OfferPlan(
            9,
            [new TimeSlot(1, [1], '10:00')],
            [],
            [new DayException('2026-06-15', null, false, DayException::CANCEL)],
            [],
            90,
            '2026-06-01',
            $until,
            $maxDates,
        );
    }

    public function testNoDatesBeforeValidFrom(): void
    {
        self::assertSame([], $this->resolver->getDay($this->summerPlan(), $this->at('2026-05-25')));
        self::assertCount(1, $this->resolver->getDay($this->summerPlan(), $this->at('2026-06-01')));
    }

    public function testEndsAfterCountIncludingCancelled(): void
    {
        $plan = $this->summerPlan();

        self::assertSame('2026-06-29 10:00', $this->resolver->getLastStart($plan, $this->at('2026-01-01'))?->format('Y-m-d H:i'));
        self::assertCount(1, $this->resolver->getDay($plan, $this->at('2026-06-29')));
        self::assertSame([], $this->resolver->getDay($plan, $this->at('2026-07-06')));
    }

    public function testUpcomingStartsAtValidFromEvenFarAhead(): void
    {
        $upcoming = $this->resolver->getUpcoming($this->summerPlan(), $this->at('2025-11-20 12:00'), 10, true);

        self::assertSame(
            ['2026-06-01', '2026-06-08', '2026-06-15', '2026-06-22', '2026-06-29'],
            array_map(static fn (Occurrence $o): string => $o->start->format('Y-m-d'), $upcoming),
        );
    }

    public function testValidUntilWinsOverCount(): void
    {
        $plan = $this->summerPlan(5, '2026-06-20');

        self::assertNull($this->resolver->getLastStart($plan, $this->at('2026-01-01')), 'nur 3 Termine bis 20.6., Anzahl wird nicht erreicht');
        self::assertCount(1, $this->resolver->getDay($plan, $this->at('2026-06-08')));
        self::assertSame([], $this->resolver->getDay($plan, $this->at('2026-06-22')));
    }

    public function testHasEnded(): void
    {
        $plan = $this->summerPlan();

        self::assertFalse($this->resolver->hasEnded($plan, $this->at('2026-06-29 09:59')));
        self::assertTrue($this->resolver->hasEnded($plan, $this->at('2026-06-29 10:00')));
        self::assertTrue($this->resolver->hasEnded($this->summerPlan(0, '2026-06-20'), $this->at('2026-06-21 00:00')));
        self::assertFalse($this->resolver->hasEnded($this->summerPlan(0, '2026-06-20'), $this->at('2026-06-20 23:00')));
        self::assertFalse($this->resolver->hasEnded($this->plan(), $this->at('2030-01-01')), 'ohne Zeitraum nie vorbei');
    }
}
