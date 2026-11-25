<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use DateTimeImmutable;
use Derkosmonaut\TourTimesBundle\Output\OfferViewBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Die heutigen Termine, chronologisch, z. B. für den Infomonitor an der Kasse.
 *
 * Ein Termin verschwindet, sobald er beginnt, mit Versatz entsprechend früher.
 * Ein kleines Skript blendet ihn auch ohne Neuladen aus und lädt die Seite
 * nach Mitternacht neu, damit die Termine des neuen Tages erscheinen.
 */
#[AsFrontendModule(type: 'tourtimes_today', category: 'tourtimes', template: 'frontend_module/tourtimes_today')]
class TodayController extends AbstractTourTimesController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        if ($this->hiddenInReader($model)) {
            return new Response();
        }

        $now = new DateTimeImmutable('now', OfferViewBuilder::timezone());
        $labels = $this->labels();
        $offset = (int) $model->row()['tourtimes_offset'];

        $offers = $this->decorate($this->repository->findPublished($this->offerIds($model)), $model, false);
        $result = $this->viewBuilder->buildToday($offers, $now, $offset, '' !== (string) $model->row()['tourtimes_show_cancelled']);

        $empty = trim((string) $model->row()['tourtimes_empty']);
        $showCancelled = '' !== (string) $model->row()['tourtimes_show_cancelled'];

        // optional: nach dem Hinweis die Termine des nächsten Tages mit Terminen
        $nextDay = '' !== (string) ($model->row()['tourtimes_show_next'] ?? '')
            ? $this->viewBuilder->buildNextDay($offers, $now, $showCancelled)
            : null;

        $template->set('labels', $labels);
        $template->set('entries', $result['entries']);
        $template->set('empty_text', '' !== $empty ? $empty : ($labels['empty_today'] ?? ''));
        $template->set('today', $now);
        $template->set('next_day', $nextDay);
        // kurz nach Mitternacht neu laden (mit etwas Abstand, damit der Cache sicher abgelaufen ist)
        $template->set('reload_at', OfferViewBuilder::nextDay($now)->modify('+1 minute'));

        $this->tagResponse(self::TAGS);

        $response = $template->getResponse();
        $response->setSharedMaxAge(OfferViewBuilder::maxAge($now, $result['nextChange']));

        return $response;
    }
}
