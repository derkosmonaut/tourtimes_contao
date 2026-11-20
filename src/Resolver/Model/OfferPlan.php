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
     */
    public function __construct(
        public int $offerId,
        public ?array $defaultSlots,
        public array $seasons = [],
        public array $exceptions = [],
        public array $cancellations = [],
        public int $duration = 0,
    ) {
    }
}
