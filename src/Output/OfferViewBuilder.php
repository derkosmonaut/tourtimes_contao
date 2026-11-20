<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Output;

use DateTimeImmutable;
use DateTimeZone;
use Derkosmonaut\TourTimesBundle\Repository\OfferRepository;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Occurrence;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;

/**
 * Bereitet Angebote und Termine als einfache Arrays für die Twig-Templates auf.
 * Kennt kein Contao, damit es ohne Installation testbar bleibt.
 *
 * @phpstan-import-type Offer from OfferRepository
 *
 * @phpstan-type OccurrenceView array{start: DateTimeImmutable, end: DateTimeImmutable|null, state: string, cancelled: bool, changed: bool, extra: bool, originalStart: DateTimeImmutable|null, notes: list<string>, hideAt: DateTimeImmutable, weekday: int, isToday: bool, isTomorrow: bool}
 */
class OfferViewBuilder
{
    /** Kürzeste und längste Cache-Dauer der Tagesliste in Sekunden. */
    public const MIN_AGE = 30;
    public const MAX_AGE = 3600;

    public function __construct(
        private readonly OfferRepository $repository,
        private readonly OccurrenceResolver $resolver,
    ) {
    }

    /**
     * Angebote mit regelmäßigen Zeiten und nächsten Terminen (Liste und Detailansicht).
     *
     * @param list<Offer>        $offers
     * @param array<int, string> $dayNames Kurznamen der Wochentage
     *
     * @return list<array<string, mixed>>
     */
    public function buildOffers(array $offers, DateTimeImmutable $now, int $upcoming, bool $includeCancelled, array $dayNames): array
    {
        $timezone = $now->getTimezone();
        $views = [];

        foreach ($offers as $offer) {
            $plan = $this->repository->loadPlan($offer['id'], $offer['duration'], $timezone);
            $season = $this->resolver->getActiveSeason($plan, $now);

            $views[] = [
                ...$offer,
                'regular' => RegularTimesFormatter::group($this->resolver->getRegularSlots($plan, $now) ?? [], $dayNames),
                'season' => $season?->title,
                'upcoming' => array_map(
                    fn (Occurrence $o): array => $this->occurrence($o, $now),
                    $upcoming > 0 ? $this->resolver->getUpcoming($plan, $now, $upcoming, $includeCancelled) : [],
                ),
            ];
        }

        return $views;
    }

    /**
     * Die heutigen Termine aller Angebote, chronologisch. Ein Termin verschwindet,
     * sobald er beginnt (abzüglich Versatz), und kommt erst am nächsten passenden Tag wieder.
     *
     * @param list<Offer> $offers
     *
     * @return array{entries: list<array<string, mixed>>, nextChange: DateTimeImmutable}
     */
    public function buildToday(array $offers, DateTimeImmutable $now, int $offsetMinutes, bool $includeCancelled): array
    {
        $entries = [];

        foreach ($offers as $offer) {
            $plan = $this->repository->loadPlan($offer['id'], $offer['duration'], $now->getTimezone());

            foreach ($this->resolver->getDay($plan, $now) as $occurrence) {
                if (!OccurrenceResolver::isVisible($occurrence, $now, $offsetMinutes)) {
                    continue;
                }

                if (!$includeCancelled && $occurrence->isCancelled()) {
                    continue;
                }

                $entries[] = ['offer' => $offer, ...$this->occurrence($occurrence, $now, $offsetMinutes)];
            }
        }

        usort($entries, static fn (array $a, array $b): int => [$a['start'], $a['offer']['title']] <=> [$b['start'], $b['offer']['title']]);

        // nächste Änderung: ein Termin wird ausgeblendet oder der Tag wechselt
        $nextChange = self::nextDay($now);

        foreach ($entries as $entry) {
            if ($entry['hideAt'] > $now && $entry['hideAt'] < $nextChange) {
                $nextChange = $entry['hideAt'];
            }
        }

        return ['entries' => $entries, 'nextChange' => $nextChange];
    }

    /**
     * Cache-Dauer bis zur nächsten Änderung, begrenzt auf MIN_AGE bis MAX_AGE.
     */
    public static function maxAge(DateTimeImmutable $now, DateTimeImmutable $nextChange): int
    {
        return max(self::MIN_AGE, min(self::MAX_AGE, $nextChange->getTimestamp() - $now->getTimestamp()));
    }

    /**
     * Frühester Beginn eines angezeigten Termins oder der nächste Tag:
     * danach muss die Liste der nächsten Termine neu berechnet werden.
     *
     * @param list<array<string, mixed>> $offers Ergebnis von buildOffers()
     */
    public static function nextUpcomingChange(array $offers, DateTimeImmutable $now): DateTimeImmutable
    {
        $next = self::nextDay($now);

        foreach ($offers as $offer) {
            foreach ($offer['upcoming'] ?? [] as $occurrence) {
                if ($occurrence['start'] > $now && $occurrence['start'] < $next) {
                    $next = $occurrence['start'];
                }
            }
        }

        return $next;
    }

    public static function nextDay(DateTimeImmutable $now): DateTimeImmutable
    {
        return $now->setTime(0, 0)->modify('+1 day');
    }

    public static function timezone(): DateTimeZone
    {
        return new DateTimeZone(date_default_timezone_get());
    }

    /**
     * @return OccurrenceView
     */
    public function occurrence(Occurrence $occurrence, DateTimeImmutable $now, int $offsetMinutes = 0): array
    {
        $day = $occurrence->start->format('Y-m-d');

        return [
            'start' => $occurrence->start,
            'end' => $occurrence->end,
            'state' => $occurrence->state,
            'cancelled' => $occurrence->isCancelled(),
            'changed' => Occurrence::CHANGED === $occurrence->state,
            'extra' => Occurrence::EXTRA === $occurrence->state,
            'originalStart' => $occurrence->originalStart,
            'notes' => $occurrence->notes,
            'hideAt' => OccurrenceResolver::hideAt($occurrence, $offsetMinutes),
            'weekday' => (int) $occurrence->start->format('N'),
            'isToday' => $day === $now->format('Y-m-d'),
            'isTomorrow' => $day === $now->modify('+1 day')->format('Y-m-d'),
        ];
    }
}
