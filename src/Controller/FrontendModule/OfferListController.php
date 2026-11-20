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
 * Liste aller (ausgewählten) Angebote mit regelmäßigen Zeiten und den nächsten Terminen.
 */
#[AsFrontendModule(type: 'tourtimes_list', category: 'tourtimes', template: 'frontend_module/tourtimes_list')]
class OfferListController extends AbstractTourTimesController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $now = new DateTimeImmutable('now', OfferViewBuilder::timezone());
        $labels = $this->labels();
        $upcoming = (int) $model->row()['tourtimes_upcoming'];

        $offers = $this->viewBuilder->buildOffers(
            $this->repository->findPublished($this->offerIds($model)),
            $now,
            $upcoming,
            '' !== (string) $model->row()['tourtimes_show_cancelled'],
            $labels['day_short'] ?? [],
        );

        $template->set('labels', $labels);
        $template->set('offers', $this->decorate($offers, $model, true));
        $template->set('show_upcoming', $upcoming > 0);

        $this->tagResponse(self::TAGS);

        $response = $template->getResponse();
        // die nächsten Termine verschieben sich mit der Zeit: höchstens bis zum nächsten Tag cachen
        $response->setSharedMaxAge(OfferViewBuilder::maxAge($now, OfferViewBuilder::nextUpcomingChange($offers, $now)));

        return $response;
    }
}
