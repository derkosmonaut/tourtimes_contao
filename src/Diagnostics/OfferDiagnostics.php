<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Diagnostics;

use DateTimeImmutable;
use Derkosmonaut\TourTimesBundle\Resolver\Model\DayException;
use Derkosmonaut\TourTimesBundle\Resolver\Model\OfferPlan;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;

/**
 * Findet Unstimmigkeiten bei einem Angebot, die zu unerwarteten Ausgaben führen können.
 *
 * Liefert Schlüssel mit Parametern; die Texte stehen in den Sprachdateien
 * ($GLOBALS['TL_LANG']['tl_tt_offer']['warnings']).
 */
final class OfferDiagnostics
{
    public function __construct(private readonly OccurrenceResolver $resolver)
    {
    }

    /**
     * @return list<array{key: string, params: list<string>}>
     */
    public function analyze(OfferPlan $plan, DateTimeImmutable $now): array
    {
        $warnings = [];
        $hasExtras = [] !== array_filter($plan->exceptions, static fn (DayException $e): bool => DayException::EXTRA === $e->type);

        if (null === $plan->defaultSlots && [] === $plan->seasons && !$hasExtras) {
            return [['key' => 'no_plan', 'params' => []]];
        }

        if ([] === $plan->defaultSlots) {
            $warnings[] = ['key' => 'default_empty', 'params' => []];
        }

        foreach ($plan->seasons as $season) {
            if ([] === $season->slots) {
                $warnings[] = ['key' => 'season_empty', 'params' => [$season->title ?: $season->start]];
            }
        }

        if ($this->resolver->hasEnded($plan, $now)) {
            $warnings[] = ['key' => 'ended', 'params' => []];
        } elseif (null !== $plan->validFrom && $plan->validFrom > $now->format('Y-m-d')) {
            $warnings[] = ['key' => 'not_started', 'params' => [(new DateTimeImmutable($plan->validFrom))->format('d.m.Y')]];
        }

        if ($plan->maxDates > 0 && null !== $plan->validFrom && null === $this->resolver->getLastStart($plan, $now)) {
            $warnings[] = ['key' => 'count_not_reached', 'params' => [(string) $plan->maxDates]];
        }

        foreach ($plan->cancellations as $cancellation) {
            if ($cancellation->active && (null === $cancellation->stop || $cancellation->stop > $now)) {
                $key = $cancellation->start <= $now ? 'cancellation_active' : 'cancellation_planned';
                $warnings[] = ['key' => $key, 'params' => [$cancellation->message ?: '–']];
            }
        }

        if (!$this->resolver->hasEnded($plan, $now) && [] === $this->resolver->getUpcoming($plan, $now, 1)) {
            $warnings[] = ['key' => 'no_upcoming', 'params' => [(string) OccurrenceResolver::HORIZON_DAYS]];
        }

        return $warnings;
    }
}
