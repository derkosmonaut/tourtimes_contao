<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Resolver\Model;

use DateTimeImmutable;

/**
 * Ein konkreter Termin an einem Tag.
 */
final readonly class Occurrence
{
    public const SCHEDULED = 'scheduled';
    public const CHANGED = 'changed';
    public const EXTRA = 'extra';
    public const CANCELLED = 'cancelled';

    /**
     * @param int                    $offerId
     * @param int|null               $slotId        null bei Zusatzterminen
     * @param DateTimeImmutable      $start
     * @param DateTimeImmutable|null $end           null = ohne Ende
     * @param string                 $state         SCHEDULED, CHANGED, EXTRA oder CANCELLED
     * @param DateTimeImmutable|null $originalStart ursprünglicher Beginn bei verschobenen Terminen
     * @param list<string>           $notes         Hinweise (Slot, Ausnahmen, Absagegrund)
     */
    public function __construct(
        public int $offerId,
        public ?int $slotId,
        public DateTimeImmutable $start,
        public ?DateTimeImmutable $end,
        public string $state = self::SCHEDULED,
        public ?DateTimeImmutable $originalStart = null,
        public array $notes = [],
    ) {
    }

    public function isCancelled(): bool
    {
        return self::CANCELLED === $this->state;
    }

    public function cancel(string $reason): self
    {
        $notes = $this->notes;

        if ('' !== $reason && !\in_array($reason, $notes, true)) {
            $notes[] = $reason;
        }

        return new self($this->offerId, $this->slotId, $this->start, $this->end, self::CANCELLED, $this->originalStart, $notes);
    }
}
