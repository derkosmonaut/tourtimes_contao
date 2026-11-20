<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Resolver;

use DateTimeImmutable;
use Derkosmonaut\TourTimesBundle\Resolver\Model\DayException;
use Derkosmonaut\TourTimesBundle\Resolver\Model\OfferPlan;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Occurrence;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Season;
use Derkosmonaut\TourTimesBundle\Resolver\Model\TimeSlot;

/**
 * Berechnet die konkreten Termine eines Angebots.
 *
 * Reihenfolge, die speziellere Ebene gewinnt:
 *   1. Spontane Absage (Zeitraum, ganzes Angebot oder einzelne Slots)
 *   2. Ausnahme       (Datum: Ausfall, Verschiebung, Hinweis, Zusatztermin)
 *   3. Saison         (ersetzt den Standardplan, höhere Priorität gewinnt)
 *   4. Standardplan
 *
 * Bei mehreren Ausnahmen an einem Tag wirken alle: Ein Ausfall schlägt eine
 * Verschiebung, Hinweise werden gesammelt, Zusatztermine kommen hinzu.
 * Ein Termin gehört zu seinem Starttag. Alle Zeiten sind lokale Zeiten der
 * Zeitzone des übergebenen DateTimeImmutable.
 */
final class OccurrenceResolver
{
    /** Wie viele Tage voraus nach kommenden Terminen gesucht wird. */
    public const HORIZON_DAYS = 120;

    /**
     * Alle Termine, die an diesem Tag beginnen, nach Beginn sortiert (inkl. abgesagter).
     *
     * @return list<Occurrence>
     */
    public function getDay(OfferPlan $plan, DateTimeImmutable $date): array
    {
        $day = $date->setTime(0, 0);
        $ymd = $day->format('Y-m-d');
        $weekday = (int) $day->format('N');

        $exceptions = array_values(array_filter(
            $plan->exceptions,
            fn (DayException $e): bool => $this->inRange($ymd, $e->start, $e->stop, $e->recurring),
        ));

        $occurrences = [];

        foreach ($this->getRegularSlots($plan, $day) ?? [] as $slot) {
            if (!\in_array($weekday, $slot->weekdays, true)) {
                continue;
            }

            $occurrences[] = $this->applyExceptions($plan, $slot, $day, $exceptions);
        }

        foreach ($exceptions as $exception) {
            if (DayException::EXTRA !== $exception->type || null === $exception->starts) {
                continue;
            }

            [$start, $end] = $this->createTimes($day, $exception->starts, $exception->ends, $plan->duration);
            $occurrences[] = new Occurrence(
                $plan->offerId,
                null,
                $start,
                $end,
                Occurrence::EXTRA,
                null,
                '' !== $exception->note ? [$exception->note] : [],
            );
        }

        // spontane Absagen zuletzt: sie schlagen alles, auch Zusatztermine
        foreach ($occurrences as $i => $occurrence) {
            foreach ($plan->cancellations as $cancellation) {
                if ($cancellation->covers($occurrence)) {
                    $occurrence = $occurrence->cancel($cancellation->message);
                }
            }

            $occurrences[$i] = $occurrence;
        }

        usort($occurrences, static fn (Occurrence $a, Occurrence $b): int => [$a->start, $a->slotId ?? 0] <=> [$b->start, $b->slotId ?? 0]);

        return $occurrences;
    }

    /**
     * Termine mehrerer Tage ab $from (Tagesbeginn), nach Beginn sortiert.
     *
     * @return list<Occurrence>
     */
    public function getRange(OfferPlan $plan, DateTimeImmutable $from, int $days): array
    {
        $start = $from->setTime(0, 0);
        $all = [];

        for ($i = 0; $i < $days; ++$i) {
            array_push($all, ...$this->getDay($plan, $start->modify('+'.$i.' day')));
        }

        return $all;
    }

    /**
     * Die nächsten Termine, die nach $now beginnen.
     *
     * @return list<Occurrence>
     */
    public function getUpcoming(OfferPlan $plan, DateTimeImmutable $now, int $limit, bool $includeCancelled = false, int $horizonDays = self::HORIZON_DAYS): array
    {
        $result = [];
        $day = $now->setTime(0, 0);

        for ($i = 0; $i < $horizonDays && \count($result) < $limit; ++$i) {
            foreach ($this->getDay($plan, $day->modify('+'.$i.' day')) as $occurrence) {
                if ($occurrence->start <= $now || (!$includeCancelled && $occurrence->isCancelled())) {
                    continue;
                }

                $result[] = $occurrence;

                if (\count($result) >= $limit) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Ein Termin bleibt sichtbar, bis er (abzüglich Versatz) beginnt.
     */
    public static function isVisible(Occurrence $occurrence, DateTimeImmutable $now, int $offsetMinutes = 0): bool
    {
        return $now < self::hideAt($occurrence, $offsetMinutes);
    }

    /**
     * Zeitpunkt, ab dem ein Termin ausgeblendet wird.
     */
    public static function hideAt(Occurrence $occurrence, int $offsetMinutes = 0): DateTimeImmutable
    {
        return $offsetMinutes > 0 ? $occurrence->start->modify('-'.$offsetMinutes.' minutes') : $occurrence->start;
    }

    /**
     * Die regelmäßigen Termine, die an diesem Datum gelten (Saison oder Standard),
     * ohne Ausnahmen und Absagen. null = kein Plan hinterlegt.
     *
     * @return list<TimeSlot>|null
     */
    public function getRegularSlots(OfferPlan $plan, DateTimeImmutable $date): ?array
    {
        return $this->getActiveSeason($plan, $date)->slots ?? $plan->defaultSlots;
    }

    /**
     * Die Saison, die an diesem Tag gilt (höchste Priorität), oder null.
     */
    public function getActiveSeason(OfferPlan $plan, DateTimeImmutable $date): ?Season
    {
        $ymd = $date->format('Y-m-d');
        $season = null;

        foreach ($plan->seasons as $candidate) {
            if (!$this->inRange($ymd, $candidate->start, $candidate->stop, $candidate->recurring)) {
                continue;
            }

            if (null === $season || $candidate->priority > $season->priority) {
                $season = $candidate;
            }
        }

        return $season;
    }

    /**
     * @param list<DayException> $exceptions Ausnahmen dieses Tages
     */
    private function applyExceptions(OfferPlan $plan, TimeSlot $slot, DateTimeImmutable $day, array $exceptions): Occurrence
    {
        [$start, $end] = $this->createTimes($day, $slot->starts, $slot->ends, $plan->duration);
        $notes = '' !== $slot->note ? [$slot->note] : [];
        $state = Occurrence::SCHEDULED;
        $originalStart = null;
        $cancelled = false;

        foreach ($exceptions as $exception) {
            if (DayException::EXTRA === $exception->type || !$exception->appliesToSlot($slot->id)) {
                continue;
            }

            if (DayException::SHIFT === $exception->type && null !== $exception->starts) {
                [$start, $end] = $this->createTimes($day, $exception->starts, $exception->ends ?? $this->shiftedEnd($slot, $exception->starts), $plan->duration);
                $originalStart ??= $this->createTimes($day, $slot->starts, null, 0)[0];
                $state = Occurrence::CHANGED;
            }

            if (DayException::CANCEL === $exception->type) {
                $cancelled = true;
            }

            if ('' !== $exception->note && !\in_array($exception->note, $notes, true)) {
                $notes[] = $exception->note;
            }
        }

        $occurrence = new Occurrence($plan->offerId, $slot->id, $start, $end, $state, $originalStart, $notes);

        return $cancelled ? $occurrence->cancel('') : $occurrence;
    }

    /**
     * Bei einer Verschiebung ohne eigenes Ende bleibt die Länge des Slots erhalten.
     */
    private function shiftedEnd(TimeSlot $slot, string $newStart): ?string
    {
        if (null === $slot->ends) {
            return null;
        }

        $length = ($this->minutes($slot->ends) - $this->minutes($slot->starts) + 1440) % 1440;

        if (0 === $length) {
            return null;
        }

        $end = ($this->minutes($newStart) + $length) % 1440;

        return \sprintf('%02d:%02d', intdiv($end, 60), $end % 60);
    }

    /**
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable|null}
     */
    private function createTimes(DateTimeImmutable $day, string $starts, ?string $ends, int $duration): array
    {
        [$hour, $minute] = $this->parseTime($starts, false);
        $start = $day->setTime($hour, $minute);

        if (null === $ends || '' === $ends) {
            return [$start, $duration > 0 ? $start->modify('+'.$duration.' minutes') : null];
        }

        [$endHour, $endMinute] = $this->parseTime($ends, true);

        if (24 === $endHour) {
            return [$start, $day->modify('+1 day')];
        }

        $end = $day->setTime($endHour, $endMinute);

        if ($end <= $start) {
            $end = $end->modify('+1 day');
        }

        return [$start, $end];
    }

    private function minutes(string $time): int
    {
        [$hour, $minute] = $this->parseTime($time, true);

        return $hour * 60 + $minute;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function parseTime(string $time, bool $allow24): array
    {
        if ($allow24 && '24:00' === $time) {
            return [24, 0];
        }

        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $time, $m)) {
            throw new \InvalidArgumentException(\sprintf('Ungültige Uhrzeit "%s", erwartet HH:MM.', $time));
        }

        return [(int) $m[1], (int) $m[2]];
    }

    /**
     * Liegt $ymd im Zeitraum? Bei Wiederholung zählen nur Monat und Tag,
     * auch über den Jahreswechsel hinweg (z. B. 1.11. bis 31.3.).
     */
    private function inRange(string $ymd, string $start, ?string $stop, bool $recurring): bool
    {
        $stop ??= $start;

        if (!$recurring) {
            return $ymd >= $start && $ymd <= $stop;
        }

        $md = substr($ymd, 5);
        $from = substr($start, 5);
        $to = substr($stop, 5);

        return $from <= $to ? ($md >= $from && $md <= $to) : ($md >= $from || $md <= $to);
    }
}
