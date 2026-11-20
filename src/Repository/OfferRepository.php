<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Repository;

use DateTimeImmutable;
use DateTimeZone;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Cancellation;
use Derkosmonaut\TourTimesBundle\Resolver\Model\DayException;
use Derkosmonaut\TourTimesBundle\Resolver\Model\OfferPlan;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Season;
use Derkosmonaut\TourTimesBundle\Resolver\Model\TimeSlot;
use Derkosmonaut\TourTimesBundle\Util\TimeFormat;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Liest die Backend-Daten eines Angebots und übersetzt sie in die Modelle des Resolvers.
 *
 * @phpstan-type Offer array{id: int, title: string, alias: string, teaser: string, description: string, image: string|null, meetingPoint: string, duration: int, price: string, language: string, ticketUrl: string, ticketLabel: string}
 */
class OfferRepository
{
    private const OFFER_FIELDS = 'id, title, alias, teaser, description, singleSRC, meeting_point, duration, price, language, ticket_url, ticket_label';

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Veröffentlichte Angebote. Mit Auswahl in der Reihenfolge der Auswahl, sonst nach Titel.
     *
     * @param list<int> $ids leer = alle veröffentlichten
     *
     * @return list<Offer>
     */
    public function findPublished(array $ids = []): array
    {
        if ([] === $ids) {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT '.self::OFFER_FIELDS." FROM tl_tt_offer WHERE published = '1' ORDER BY title",
            );
        } else {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT '.self::OFFER_FIELDS." FROM tl_tt_offer WHERE published = '1' AND id IN (?)",
                [$ids],
                [ArrayParameterType::INTEGER],
            );

            $position = array_flip($ids);
            usort($rows, static fn (array $a, array $b): int => ($position[(int) $a['id']] ?? 0) <=> ($position[(int) $b['id']] ?? 0));
        }

        return array_map($this->mapOffer(...), $rows);
    }

    /**
     * @return Offer|null
     */
    public function findPublishedByAlias(string $alias): ?array
    {
        $column = ctype_digit($alias) ? 'id' : 'alias';
        $row = $this->connection->fetchAssociative(
            'SELECT '.self::OFFER_FIELDS." FROM tl_tt_offer WHERE published = '1' AND $column = ?",
            [$alias],
        );

        return false === $row ? null : $this->mapOffer($row);
    }

    public function loadPlan(int $offerId, int $duration, DateTimeZone $timezone): OfferPlan
    {
        $schedules = $this->connection->fetchAllAssociative(
            'SELECT id, type, title, `start`, `stop`, recurring, priority FROM tl_tt_schedule WHERE pid = ? ORDER BY id',
            [$offerId],
        );

        $slotRows = $this->connection->fetchAllAssociative(
            'SELECT s.id, s.pid, s.weekdays, s.starts, s.ends, s.note
               FROM tl_tt_slot s
         INNER JOIN tl_tt_schedule p ON p.id = s.pid
              WHERE p.pid = ?
           ORDER BY s.starts',
            [$offerId],
        );

        $slotsBySchedule = [];

        foreach ($slotRows as $row) {
            $slot = $this->createSlot($row);

            if (null !== $slot) {
                $slotsBySchedule[(int) $row['pid']][] = $slot;
            }
        }

        $defaultSlots = null;
        $seasons = [];

        foreach ($schedules as $schedule) {
            $slots = $slotsBySchedule[(int) $schedule['id']] ?? [];

            if ('season' === $schedule['type']) {
                $start = $this->formatDate($schedule['start'], $timezone);
                $stop = $this->formatDate($schedule['stop'], $timezone);

                // ein Ende vor dem Beginn ist ungültig und würde bei Wiederholung fast das ganze Jahr treffen
                if (null === $start || (null !== $stop && $stop < $start)) {
                    continue;
                }

                $seasons[] = new Season($start, $stop ?? $start, '' !== (string) $schedule['recurring'], (int) $schedule['priority'], $slots, (string) $schedule['title']);

                continue;
            }

            // mehrere Standardpläne werden zusammengeführt
            $defaultSlots = [...($defaultSlots ?? []), ...$slots];
        }

        $range = $this->connection->fetchAssociative(
            'SELECT date_from, date_until, max_dates FROM tl_tt_offer WHERE id = ?',
            [$offerId],
        ) ?: [];

        $validFrom = $this->formatDate($range['date_from'] ?? null, $timezone);
        $validUntil = $this->formatDate($range['date_until'] ?? null, $timezone);

        // ein Ende vor dem Beginn ist ungültig und wird ignoriert
        if (null !== $validFrom && null !== $validUntil && $validUntil < $validFrom) {
            $validUntil = null;
        }

        return new OfferPlan(
            $offerId,
            $defaultSlots,
            $seasons,
            $this->loadExceptions($offerId, $timezone),
            $this->loadCancellations($offerId, $timezone),
            $duration,
            $validFrom,
            $validUntil,
            (int) ($range['max_dates'] ?? 0),
        );
    }

    /**
     * @return list<DayException>
     */
    private function loadExceptions(int $offerId, DateTimeZone $timezone): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, type, scope, slots, `start`, `stop`, recurring, starts, ends, note FROM tl_tt_exception WHERE pid = ? ORDER BY `start`, id',
            [$offerId],
        );

        $exceptions = [];

        foreach ($rows as $row) {
            $start = $this->formatDate($row['start'], $timezone);
            $type = (string) $row['type'];

            if (null === $start || !\in_array($type, [DayException::CANCEL, DayException::SHIFT, DayException::NOTE, DayException::EXTRA], true)) {
                continue;
            }

            $stop = $this->formatDate($row['stop'], $timezone);

            // ein Ende vor dem Beginn ist ungültig: dann gilt nur der Starttag
            if (null !== $stop && $stop < $start) {
                $stop = null;
            }

            // Verschiebungen betreffen immer ausgewählte Slots, die anderen Arten nur bei Bereich "Slots"
            $slotIds = DayException::SHIFT === $type || 'slots' === $row['scope'] ? $this->ids($row['slots']) : [];
            $starts = TimeFormat::normalize((string) $row['starts']);
            $ends = TimeFormat::normalize((string) $row['ends'], true);

            // eine Verschiebung braucht eine neue Uhrzeit und betroffene Slots, ein Zusatztermin eine Uhrzeit
            if ((DayException::SHIFT === $type && (null === $starts || [] === $slotIds)) || (DayException::EXTRA === $type && null === $starts)) {
                continue;
            }

            $exceptions[] = new DayException($start, $stop, '' !== (string) $row['recurring'], $type, $slotIds, $starts, $ends, trim((string) $row['note']), (int) $row['id']);
        }

        return $exceptions;
    }

    /**
     * @return list<Cancellation>
     */
    private function loadCancellations(int $offerId, DateTimeZone $timezone): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT `start`, `stop`, active, message, scope, slots FROM tl_tt_cancellation WHERE pid = ? ORDER BY `start`',
            [$offerId],
        );

        $cancellations = [];

        foreach ($rows as $row) {
            if (empty($row['start'])) {
                continue;
            }

            $cancellations[] = new Cancellation(
                $this->createDateTime((int) $row['start'], $timezone),
                empty($row['stop']) ? null : $this->createDateTime((int) $row['stop'], $timezone),
                '' !== (string) $row['active'],
                trim((string) $row['message']),
                'slots' === $row['scope'] ? $this->ids($row['slots']) : [],
            );
        }

        return $cancellations;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function createSlot(array $row): ?TimeSlot
    {
        $weekdays = array_values(array_filter(
            $this->ids($row['weekdays']),
            static fn (int $day): bool => $day >= 1 && $day <= 7,
        ));

        $starts = TimeFormat::normalize((string) $row['starts']);

        // ungültige Zeilen überspringen, damit ein Fehler nicht die ganze Seite stört
        if ([] === $weekdays || null === $starts) {
            return null;
        }

        return new TimeSlot((int) $row['id'], $weekdays, $starts, TimeFormat::normalize((string) $row['ends'], true), trim((string) $row['note']));
    }

    /**
     * Serialisiertes Array aus einem Checkbox-Feld als Liste von Ganzzahlen.
     *
     * @return list<int>
     */
    private function ids(mixed $value): array
    {
        if (!\is_string($value) || '' === $value) {
            return [];
        }

        $data = @unserialize($value, ['allowed_classes' => false]);

        return \is_array($data) ? array_values(array_unique(array_map('intval', $data))) : [];
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return Offer
     */
    private function mapOffer(array $row): array
    {
        $image = $row['singleSRC'] ?? null;

        return [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'alias' => (string) $row['alias'],
            'teaser' => (string) $row['teaser'],
            'description' => (string) $row['description'],
            'image' => \is_string($image) && '' !== $image ? $image : null,
            'meetingPoint' => (string) $row['meeting_point'],
            'duration' => (int) $row['duration'],
            'price' => (string) $row['price'],
            'language' => (string) $row['language'],
            'ticketUrl' => (string) $row['ticket_url'],
            'ticketLabel' => (string) $row['ticket_label'],
        ];
    }

    private function formatDate(mixed $timestamp, DateTimeZone $timezone): ?string
    {
        if (empty($timestamp)) {
            return null;
        }

        return $this->createDateTime((int) $timestamp, $timezone)->format('Y-m-d');
    }

    private function createDateTime(int $timestamp, DateTimeZone $timezone): DateTimeImmutable
    {
        return (new DateTimeImmutable('@'.$timestamp))->setTimezone($timezone);
    }
}
