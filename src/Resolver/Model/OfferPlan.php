<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Resolver\Model;

/**
 * Alle Daten eines Angebots, die der Resolver braucht. Unabhängig von Contao und Datenbank.
 */
final readonly class OfferPlan
{
    /**
     * @param int                 $offerId
     * @param list<TimeSlot>|null $defaultSlots Standardplan; null = keiner hinterlegt
     * @param list<Season>        $seasons
     * @param list<DayException>  $exceptions
     * @param list<Cancellation>  $cancellations
     * @param int                 $duration     Dauer in Minuten für Termine ohne Ende (0 = ohne Ende)
     * @param string|null         $validFrom    erster möglicher Tag als "Y-m-d"; null = unbegrenzt
     * @param string|null         $validUntil   letzter möglicher Tag als "Y-m-d"; null = unbegrenzt
     * @param int                 $maxDates     Angebot endet nach so vielen Terminen ab $validFrom (0 = unbegrenzt).
     *                                          Jeder Termin zählt, auch abgesagte und Zusatztermine.
     * @param bool                $window       Zeitfenster statt fester Startzeit (z. B. Audioguide 10–12 Uhr):
     *                                          Termine gelten bis zum Ende, ohne Ende wie feste Startzeit;
     *                                          die Dauer bestimmt dann kein Ende
     * @param list<string>        $holidays     Feiertage als "Y-m-d": keine regulären Termine, sofern keine
     *                                          Ausnahme (Verschiebung, Hinweis) den Slot an diesem Tag bestätigt
     */
    public function __construct(
        public int $offerId,
        public ?array $defaultSlots,
        public array $seasons = [],
        public array $exceptions = [],
        public array $cancellations = [],
        public int $duration = 0,
        public ?string $validFrom = null,
        public ?string $validUntil = null,
        public int $maxDates = 0,
        public array $holidays = [],
        public bool $window = false,
    ) {
    }
}
