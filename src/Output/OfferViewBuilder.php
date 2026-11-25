<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Output;

use DateTimeImmutable;
use DateTimeZone;
use Derkosmonaut\TourTimesBundle\Repository\OfferRepository;
use Derkosmonaut\TourTimesBundle\Resolver\Model\OfferPlan;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Occurrence;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;

/**
 * Bereitet Angebote und Termine als einfache Arrays für die Twig-Templates auf.
 * Kennt kein Contao, damit es ohne Installation testbar bleibt.
 *
 * @phpstan-import-type Offer from OfferRepository
 *
 * @phpstan-type OccurrenceView array{start: DateTimeImmutable, end: DateTimeImmutable|null, state: string, cancelled: bool, changed: bool, extra: bool, originalStart: DateTimeImmutable|null, notes: list<string>, window: bool, running: bool, until: DateTimeImmutable, hideAt: DateTimeImmutable, weekday: int, isToday: bool, isTomorrow: bool}
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
     * @param array<int, string> $dayNames  Kurznamen der Wochentage
     * @param bool               $skipEnded abgelaufene Angebote weglassen (Liste), in der Detailansicht behalten
     *
     * @return list<array<string, mixed>>
     */
    public function buildOffers(array $offers, DateTimeImmutable $now, int $upcoming, bool $includeCancelled, array $dayNames, bool $skipEnded = true): array
    {
        $timezone = $now->getTimezone();
        $views = [];

        foreach ($offers as $offer) {
            $plan = $this->repository->loadPlan($offer['id'], $offer['duration'], $timezone);
            $ended = $this->resolver->hasEnded($plan, $now);

            if ($ended && $skipEnded) {
                continue;
            }

            $season = $this->resolver->getActiveSeason($plan, $now);

            $views[] = [
                ...$offer,
                'regular' => RegularTimesFormatter::group($this->resolver->getRegularSlots($plan, $now) ?? [], $dayNames),
                'season' => $season?->title,
                'ended' => $ended,
                'validFrom' => null !== $plan->validFrom ? new DateTimeImmutable($plan->validFrom, $timezone) : null,
                'validUntil' => $this->lastDay($plan, $now),
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
            // ausgeblendet wird bei hideAt; ein Zeitfenster wechselt außerdem bei Beginn auf "jetzt möglich"
            foreach ([$entry['hideAt'], $entry['window'] ? $entry['start'] : null] as $moment) {
                if (null !== $moment && $moment > $now && $moment < $nextChange) {
                    $nextChange = $moment;
                }
            }
        }

        return ['entries' => $entries, 'nextChange' => $nextChange];
    }

    /**
     * Letzter Tag des Angebots: "Termine bis" oder der Tag des letzten Termins, sonst null.
     */
    private function lastDay(OfferPlan $plan, DateTimeImmutable $now): ?DateTimeImmutable
    {
        $last = $this->resolver->getLastStart($plan, $now)?->setTime(0, 0);
        $until = null !== $plan->validUntil ? new DateTimeImmutable($plan->validUntil, $now->getTimezone()) : null;

        if (null === $last) {
            return $until;
        }

        return null !== $until && $until < $last ? $until : $last;
    }

    /**
     * Die angezeigten Termine eines Angebots als schema.org-Events (für add_schema_org im Template).
     *
     * @param array<string, mixed> $offer Ergebnis von buildOffers(), mit "url"
     *
     * @return list<array<string, mixed>>
     */
    public static function schemaEvents(array $offer): array
    {
        $events = [];
        $description = trim(html_entity_decode(strip_tags((string) ($offer['teaser'] ?? '')), ENT_QUOTES | ENT_HTML5));

        foreach ($offer['upcoming'] ?? [] as $date) {
            $event = [
                '@type' => 'Event',
                '@id' => '#/schema/tourtimes/'.$offer['id'].'/'.$date['start']->format('YmdHi'),
                'name' => (string) $offer['title'],
                'startDate' => $date['start']->format(DATE_ATOM),
                'eventStatus' => 'https://schema.org/'.match (true) {
                    $date['cancelled'] => 'EventCancelled',
                    $date['changed'] => 'EventRescheduled',
                    default => 'EventScheduled',
                },
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'location' => ['@type' => 'Place', 'name' => '' !== (string) ($offer['meetingPoint'] ?? '') ? (string) $offer['meetingPoint'] : (string) $offer['title']],
            ];

            if (null !== $date['end']) {
                $event['endDate'] = $date['end']->format(DATE_ATOM);
            }

            if ($date['changed'] && null !== $date['originalStart']) {
                $event['previousStartDate'] = $date['originalStart']->format(DATE_ATOM);
            }

            if ('' !== $description) {
                $event['description'] = $description;
            }

            if (!empty($offer['url'])) {
                $event['url'] = (string) $offer['url'];
            }

            if ('' !== (string) ($offer['ticketUrl'] ?? '')) {
                $event['offers'] = ['@type' => 'Offer', 'url' => (string) $offer['ticketUrl']];
            }

            $events[] = $event;
        }

        return $events;
    }

    /**
     * Der nächste Tag nach heute, an dem Termine stattfinden (für "Termine heute",
     * wenn heute nichts mehr ansteht). null = in den nächsten $maxDays Tagen keiner.
     *
     * @param list<array<string, mixed>> $offers
     *
     * @return array{date: DateTimeImmutable, isTomorrow: bool, weekday: int, entries: list<array<string, mixed>>}|null
     */
    public function buildNextDay(array $offers, DateTimeImmutable $now, bool $includeCancelled, int $maxDays = 14): ?array
    {
        $plans = [];

        foreach ($offers as $offer) {
            $plans[] = [$offer, $this->repository->loadPlan((int) $offer['id'], (int) $offer['duration'], $now->getTimezone())];
        }

        for ($i = 1; $i <= $maxDays; ++$i) {
            $day = $now->setTime(0, 0)->modify('+'.$i.' day');
            $entries = [];

            foreach ($plans as [$offer, $plan]) {
                foreach ($this->resolver->getDay($plan, $day) as $occurrence) {
                    if ($includeCancelled || !$occurrence->isCancelled()) {
                        $entries[] = ['offer' => $offer, ...$this->occurrence($occurrence, $now)];
                    }
                }
            }

            // ein Tag nur mit abgesagten Terminen zählt nicht als "nächster Tag"
            if ([] === array_filter($entries, static fn (array $e): bool => !$e['cancelled'])) {
                continue;
            }

            usort($entries, static fn (array $a, array $b): int => [$a['start'], $a['offer']['title']] <=> [$b['start'], $b['offer']['title']]);

            return ['date' => $day, 'isTomorrow' => 1 === $i, 'weekday' => (int) $day->format('N'), 'entries' => $entries];
        }

        return null;
    }

    /**
     * Cache-Dauer bis zur nächsten Änderung, begrenzt auf MIN_AGE bis MAX_AGE.
     */
    public static function maxAge(DateTimeImmutable $now, DateTimeImmutable $nextChange): int
    {
        return max(self::MIN_AGE, min(self::MAX_AGE, $nextChange->getTimestamp() - $now->getTimestamp()));
    }

    /**
     * Frühester Beginn (Zeitfenster: Ende) eines angezeigten Termins oder der nächste Tag:
     * danach muss die Liste der nächsten Termine neu berechnet werden.
     *
     * @param list<array<string, mixed>> $offers Ergebnis von buildOffers()
     */
    public static function nextUpcomingChange(array $offers, DateTimeImmutable $now): DateTimeImmutable
    {
        $next = self::nextDay($now);

        foreach ($offers as $offer) {
            foreach ($offer['upcoming'] ?? [] as $occurrence) {
                // die nächste Änderung: ein Termin beginnt bzw. ein Zeitfenster beginnt oder endet
                foreach ([$occurrence['start'], $occurrence['until'] ?? null] as $moment) {
                    if (null !== $moment && $moment > $now && $moment < $next) {
                        $next = $moment;
                    }
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
            'window' => $occurrence->window && null !== $occurrence->end,
            'running' => $occurrence->isRunning($now),
            'until' => $occurrence->until(),
            'hideAt' => OccurrenceResolver::hideAt($occurrence, $offsetMinutes),
            'weekday' => (int) $occurrence->start->format('N'),
            'isToday' => $day === $now->format('Y-m-d'),
            'isTomorrow' => $day === $now->modify('+1 day')->format('Y-m-d'),
        ];
    }
}
