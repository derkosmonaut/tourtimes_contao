<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Resolver\Model;

/**
 * Geplante Ausnahme für ein Datum oder einen Datumsbereich.
 *
 * Arten:
 *   CANCEL = Termine fallen aus
 *   SHIFT  = Termine finden zu einer anderen Uhrzeit statt
 *   NOTE   = Termine finden statt, mit Hinweis (z. B. "Treffpunkt verlegt")
 *   EXTRA  = zusätzlicher Termin, unabhängig von den Slots
 *
 * CANCEL, SHIFT und NOTE betreffen die Slots in $slotIds; eine leere Liste heißt
 * "alle Termine des Angebots". EXTRA nutzt $starts/$ends.
 */
final readonly class DayException
{
    public const CANCEL = 'cancel';
    public const SHIFT = 'shift';
    public const NOTE = 'note';
    public const EXTRA = 'extra';

    /**
     * @param string      $start     erster Tag als "Y-m-d"
     * @param string|null $stop      letzter Tag als "Y-m-d"; null = nur ein Tag
     * @param bool        $recurring jährlich wiederholen
     * @param string      $type      CANCEL, SHIFT, NOTE oder EXTRA
     * @param list<int>   $slotIds   betroffene Slots; leer = alle (bei SHIFT ohne Wirkung)
     * @param string|null $starts    neue Uhrzeit (SHIFT) bzw. Uhrzeit des Zusatztermins (EXTRA)
     * @param string|null $ends      Ende dazu; null = aus der Dauer des Angebots
     * @param string      $note      öffentlicher Hinweis
     * @param int         $id        Datensatz-ID (für Zusatztermine als Bezug)
     */
    public function __construct(
        public string $start,
        public ?string $stop,
        public bool $recurring,
        public string $type,
        public array $slotIds = [],
        public ?string $starts = null,
        public ?string $ends = null,
        public string $note = '',
        public int $id = 0,
    ) {
    }

    public function appliesToSlot(int $slotId): bool
    {
        return [] === $this->slotIds || \in_array($slotId, $this->slotIds, true);
    }
}
