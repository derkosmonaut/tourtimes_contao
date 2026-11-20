<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use DateTimeImmutable;
use Derkosmonaut\TourTimesBundle\Diagnostics\OfferDiagnostics;
use Derkosmonaut\TourTimesBundle\Output\OfferViewBuilder;
use Derkosmonaut\TourTimesBundle\Repository\OfferRepository;
use Derkosmonaut\TourTimesBundle\Resolver\Model\Occurrence;
use Derkosmonaut\TourTimesBundle\Resolver\OccurrenceResolver;

/**
 * Vorschau im Backend: die nächsten Termine mit Zustand und Hinweise auf Unstimmigkeiten.
 * So lässt sich nach dem Speichern sofort prüfen, was im Frontend erscheint.
 */
#[AsCallback(table: 'tl_tt_offer', target: 'fields.tourtimes_preview.input_field_callback')]
class OfferPreviewListener
{
    private const DATES = 10;

    public function __construct(
        private readonly OfferRepository $repository,
        private readonly OccurrenceResolver $resolver,
        private readonly OfferDiagnostics $diagnostics,
    ) {
    }

    public function __invoke(DataContainer $dc): string
    {
        $record = $dc->getCurrentRecord();
        $id = (int) $dc->id;

        if ($id < 1 || null === $record) {
            return '';
        }

        System::loadLanguageFile('tourtimes');

        $lang = $GLOBALS['TL_LANG']['tourtimes'] ?? [];
        $own = $GLOBALS['TL_LANG']['tl_tt_offer'] ?? [];
        $now = new DateTimeImmutable('now', OfferViewBuilder::timezone());
        $plan = $this->repository->loadPlan($id, (int) ($record['duration'] ?? 0), $now->getTimezone());

        $html = '<div class="widget clr"><h3>'.$this->e((string) ($own['tourtimes_preview'][0] ?? 'Vorschau')).'</h3>';
        $upcoming = $this->resolver->getUpcoming($plan, $now, self::DATES, true);

        if ([] !== $upcoming) {
            $html .= '<table class="tl_listing" style="width:auto"><tbody>';

            foreach ($upcoming as $occurrence) {
                $html .= \sprintf(
                    '<tr><td style="padding-right:1.5em">%s %s</td><td style="padding-right:1.5em">%s</td><td>%s</td></tr>',
                    $this->e((string) ($lang['day_short'][(int) $occurrence->start->format('N')] ?? '')),
                    $this->e($occurrence->start->format('d.m.Y')),
                    $this->e($occurrence->start->format('H:i').(null !== $occurrence->end ? ' – '.$occurrence->end->format('H:i') : '')),
                    $this->e($this->stateText($occurrence, $lang)),
                );
            }

            $html .= '</tbody></table>';
        }

        foreach ($this->diagnostics->analyze($plan, $now) as $warning) {
            $text = (string) ($own['warnings'][$warning['key']] ?? $warning['key']);
            $html .= '<p class="tl_info">'.$this->e(\sprintf($text, ...($warning['params'] ?: ['']))).'</p>';
        }

        return $html.'</div>';
    }

    /**
     * @param array<string, mixed> $lang
     */
    private function stateText(Occurrence $occurrence, array $lang): string
    {
        $parts = [];

        if ($occurrence->isCancelled()) {
            $parts[] = (string) ($lang['cancelled'] ?? '');
        } elseif (Occurrence::CHANGED === $occurrence->state) {
            $parts[] = ($lang['changed'] ?? '').' ('.($lang['changed_from'] ?? '').' '.$occurrence->originalStart?->format('H:i').')';
        } elseif (Occurrence::EXTRA === $occurrence->state) {
            $parts[] = (string) ($lang['extra'] ?? '');
        }

        return implode(' – ', [...$parts, ...$occurrence->notes]);
    }

    private function e(string $value): string
    {
        return StringUtil::specialchars($value);
    }
}
