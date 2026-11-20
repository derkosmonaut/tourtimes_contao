<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Resolver\Model;

/**
 * Regelmäßiger Termin eines Plans, z. B. "Mo, Di, Mi um 10:00".
 *
 * Zeiten als "HH:MM". Ohne Ende gilt die Dauer des Angebots (oder gar kein Ende).
 * Liegt das Ende vor oder gleich dem Beginn, endet der Termin am Folgetag.
 */
final readonly class TimeSlot
{
    /**
     * @param int         $id       Datensatz-ID, Bezug für Ausnahmen und Absagen einzelner Slots
     * @param list<int>   $weekdays ISO-Wochentage, 1 = Montag ... 7 = Sonntag
     * @param string      $starts   Beginn "HH:MM"
     * @param string|null $ends     Ende "HH:MM" (auch "24:00"); null = aus der Dauer des Angebots
     * @param string      $note     fester Hinweis am Termin, z. B. "auf Englisch"
     */
    public function __construct(
        public int $id,
        public array $weekdays,
        public string $starts,
        public ?string $ends = null,
        public string $note = '',
    ) {
    }
}
