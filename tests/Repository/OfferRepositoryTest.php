<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Tests\Repository;

use DateTimeImmutable;
use DateTimeZone;
use Derkosmonaut\TourTimesBundle\Repository\OfferRepository;
use Derkosmonaut\TourTimesBundle\Resolver\Model\DayException;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Occurrence;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

/**
 * Liest echte Datensätze (SQLite im Speicher) und prüft das Ergebnis über den Resolver.
 */
final class OfferRepositoryTest extends TestCase
{
    private Connection $connection;
    private DateTimeZone $timezone;

    protected function setUp(): void
    {
        $this->timezone = new DateTimeZone('Europe/Berlin');
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);

        foreach ([
            'CREATE TABLE tl_tt_offer (id INTEGER PRIMARY KEY, title TEXT, alias TEXT, teaser TEXT, description TEXT, singleSRC BLOB, meeting_point TEXT, duration INTEGER, price TEXT, language TEXT, ticket_url TEXT, ticket_label TEXT, published TEXT, date_from INTEGER, date_until INTEGER, max_dates INTEGER, holiday_region TEXT)',
            'CREATE TABLE tl_tt_schedule (id INTEGER PRIMARY KEY, pid INTEGER, type TEXT, title TEXT, "start" INTEGER, "stop" INTEGER, recurring TEXT, priority INTEGER)',
            'CREATE TABLE tl_tt_slot (id INTEGER PRIMARY KEY, pid INTEGER, weekdays BLOB, starts TEXT, ends TEXT, note TEXT)',
            'CREATE TABLE tl_tt_exception (id INTEGER PRIMARY KEY, pid INTEGER, type TEXT, title TEXT, scope TEXT, slots BLOB, "start" INTEGER, "stop" INTEGER, recurring TEXT, starts TEXT, ends TEXT, note TEXT)',
            'CREATE TABLE tl_tt_cancellation (id INTEGER PRIMARY KEY, pid INTEGER, message TEXT, active TEXT, scope TEXT, slots BLOB, "start" INTEGER, "stop" INTEGER)',
        ] as $sql) {
            $this->connection->executeStatement($sql);
        }

        $this->addOffer(1, 'Domführung', 'domfuehrung', '1');
        $this->addOffer(2, 'Turmführung', 'turmfuehrung', '1');
        $this->addOffer(3, 'Entwurf', 'entwurf', '');

        $this->connection->insert('tl_tt_schedule', ['id' => 1, 'pid' => 1, 'type' => 'default', 'title' => 'Standard', 'start' => null, 'stop' => null, 'recurring' => '', 'priority' => 0]);
        $this->addSlot(1, 1, [1, 2, 3], '10:00', '11:00');
        $this->addSlot(2, 1, [6], '14:00', '', 'auf Englisch');
    }

    private function addOffer(int $id, string $title, string $alias, string $published): void
    {
        $this->connection->insert('tl_tt_offer', [
            'id' => $id, 'title' => $title, 'alias' => $alias, 'teaser' => '', 'description' => '', 'singleSRC' => null,
            'meeting_point' => 'Hauptportal', 'duration' => 90, 'price' => '', 'language' => '', 'ticket_url' => '', 'ticket_label' => '', 'published' => $published,
        ]);
    }

    /**
     * @param list<int> $weekdays
     */
    private function addSlot(int $id, int $scheduleId, array $weekdays, string $starts, string $ends = '', string $note = ''): void
    {
        $this->connection->insert('tl_tt_slot', [
            'id' => $id,
            'pid' => $scheduleId,
            'weekdays' => serialize(array_map('strval', $weekdays)),
            'starts' => $starts,
            'ends' => $ends,
            'note' => $note,
        ]);
    }

    private function timestamp(string $date): int
    {
        return (new DateTimeImmutable($date, $this->timezone))->getTimestamp();
    }

    /**
     * @return list<Occurrence>
     */
    private function day(string $date): array
    {
        $plan = (new OfferRepository($this->connection))->loadPlan(1, 90, $this->timezone);

        return (new OccurrenceResolver())->getDay($plan, new DateTimeImmutable($date, $this->timezone));
    }

    public function testFindPublished(): void
    {
        $repository = new OfferRepository($this->connection);

        self::assertSame(['Domführung', 'Turmführung'], array_column($repository->findPublished(), 'title'));
        self::assertSame(['Turmführung', 'Domführung'], array_column($repository->findPublished([2, 1, 3]), 'title'), 'Reihenfolge der Auswahl, Entwurf fehlt');
        self::assertSame('Hauptportal', $repository->findPublishedByAlias('domfuehrung')['meetingPoint'] ?? null);
        self::assertSame(1, $repository->findPublishedByAlias('1')['id'] ?? null);
        self::assertNull($repository->findPublishedByAlias('entwurf'));
    }

    public function testSlotsAndDuration(): void
    {
        $monday = $this->day('2026-11-16');
        $saturday = $this->day('2026-11-21');

        self::assertSame('11:00', $monday[0]->end?->format('H:i'));
        self::assertSame('15:30', $saturday[0]->end?->format('H:i'), 'ohne Ende gilt die Dauer');
        self::assertSame(['auf Englisch'], $saturday[0]->notes);
    }

    public function testShiftAndExtra(): void
    {
        $this->connection->insert('tl_tt_exception', [
            'pid' => 1, 'type' => DayException::SHIFT, 'title' => 'Gottesdienst', 'scope' => 'offer', 'slots' => serialize(['1']),
            'start' => $this->timestamp('2026-11-17 00:00'), 'stop' => null, 'recurring' => '', 'starts' => '11:30', 'ends' => '', 'note' => '',
        ]);
        $this->connection->insert('tl_tt_exception', [
            'pid' => 1, 'type' => DayException::EXTRA, 'title' => 'Sonder', 'scope' => 'offer', 'slots' => null,
            'start' => $this->timestamp('2026-11-19 00:00'), 'stop' => null, 'recurring' => '', 'starts' => '16:00', 'ends' => '17:00', 'note' => 'Sonderführung',
        ]);

        $tuesday = $this->day('2026-11-17');
        $thursday = $this->day('2026-11-19');

        self::assertSame(Occurrence::CHANGED, $tuesday[0]->state);
        self::assertSame('11:30', $tuesday[0]->start->format('H:i'));
        self::assertSame(Occurrence::EXTRA, $thursday[0]->state);
        self::assertSame(['Sonderführung'], $thursday[0]->notes);
    }

    public function testCancelScopeSlotsAndRange(): void
    {
        $this->connection->insert('tl_tt_exception', [
            'pid' => 1, 'type' => DayException::CANCEL, 'title' => 'Domputz', 'scope' => 'slots', 'slots' => serialize(['1']),
            'start' => $this->timestamp('2026-11-16 00:00'), 'stop' => $this->timestamp('2026-11-18 00:00'), 'recurring' => '', 'starts' => '', 'ends' => '', 'note' => 'Domputz',
        ]);

        self::assertTrue($this->day('2026-11-16')[0]->isCancelled());
        self::assertTrue($this->day('2026-11-18')[0]->isCancelled(), 'letzter Tag zählt mit');
        self::assertFalse($this->day('2026-11-21')[0]->isCancelled(), 'Slot 2 nicht betroffen');
    }

    public function testSpontaneousCancellation(): void
    {
        $this->connection->insert('tl_tt_cancellation', [
            'pid' => 1, 'message' => 'Krankheitsbedingt', 'active' => '1', 'scope' => 'offer', 'slots' => null,
            'start' => $this->timestamp('2026-11-16 08:00'), 'stop' => $this->timestamp('2026-11-16 20:00'),
        ]);

        $day = $this->day('2026-11-16');

        self::assertTrue($day[0]->isCancelled());
        self::assertSame(['Krankheitsbedingt'], $day[0]->notes);
        self::assertFalse($this->day('2026-11-17')[0]->isCancelled());
    }

    public function testSeasonReplacesDefault(): void
    {
        $this->connection->insert('tl_tt_schedule', [
            'id' => 2, 'pid' => 1, 'type' => 'season', 'title' => 'Advent',
            'start' => $this->timestamp('2026-11-29 00:00'), 'stop' => $this->timestamp('2026-12-23 00:00'), 'recurring' => '1', 'priority' => 0,
        ]);
        $this->addSlot(3, 2, [1, 2, 3, 4, 5, 6, 7], '17:00');

        $day = $this->day('2026-11-30');

        self::assertCount(1, $day);
        self::assertSame('17:00', $day[0]->start->format('H:i'));
    }

    public function testOfferPeriodIsLoaded(): void
    {
        $this->connection->executeStatement(
            'UPDATE tl_tt_offer SET date_from = ?, date_until = ?, max_dates = 3 WHERE id = 1',
            [$this->timestamp('2026-11-16 00:00'), $this->timestamp('2026-12-31 00:00')],
        );

        $plan = (new OfferRepository($this->connection))->loadPlan(1, 90, $this->timezone);

        self::assertSame('2026-11-16', $plan->validFrom);
        self::assertSame('2026-12-31', $plan->validUntil);
        self::assertSame(3, $plan->maxDates);
        self::assertSame([], $this->day('2026-11-21'), 'vierter Termin (Sa) fällt weg: Mo, Di, Mi sind die drei');
    }

    public function testHolidayRegion(): void
    {
        $this->connection->executeStatement("UPDATE tl_tt_offer SET holiday_region = 'DE' WHERE id = 1");

        self::assertSame([], $this->day('2026-04-06'), 'Ostermontag');
        self::assertCount(1, $this->day('2026-04-13'));
    }
}
