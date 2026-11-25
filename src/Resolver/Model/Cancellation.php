<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Resolver\Model;

use DateTimeImmutable;

/**
 * Spontane Absage mit höchster Priorität: alle Termine, die im Zeitraum beginnen,
 * fallen aus. Gilt für das ganze Angebot oder nur für einzelne Slots.
 */
final readonly class Cancellation
{
    /**
     * @param DateTimeImmutable      $start   Beginn (kann in der Zukunft liegen)
     * @param DateTimeImmutable|null $stop    Ende (exklusiv); null = bis auf Weiteres
     * @param bool                   $active  deaktivierte Absagen werden ignoriert
     * @param string                 $message Hinweistext, z. B. "Krankheitsbedingt"
     * @param list<int>              $slotIds betroffene Slots; leer = alle Termine inkl. Zusatztermine
     */
    public function __construct(
        public DateTimeImmutable $start,
        public ?DateTimeImmutable $stop,
        public bool $active = true,
        public string $message = '',
        public array $slotIds = [],
    ) {
    }

    /**
     * Feste Startzeit: der Beginn liegt im Zeitraum. Zeitfenster: Fenster und Zeitraum überschneiden sich.
     */
    public function covers(Occurrence $occurrence): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($occurrence->window && null !== $occurrence->end) {
            if ($occurrence->end <= $this->start || (null !== $this->stop && $occurrence->start >= $this->stop)) {
                return false;
            }
        } elseif ($occurrence->start < $this->start || (null !== $this->stop && $occurrence->start >= $this->stop)) {
            return false;
        }

        if ([] === $this->slotIds) {
            return true;
        }

        return null !== $occurrence->slotId && \in_array($occurrence->slotId, $this->slotIds, true);
    }
}
