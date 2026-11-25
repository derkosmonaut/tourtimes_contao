<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Input;
use Contao\ModuleModel;
use DateTimeImmutable;
use Derkosmonaut\TourTimesBundle\Output\OfferViewBuilder;
use Derkosmonaut\TourTimesBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detailansicht eines Angebots. Das Angebot kommt über den Alias in der URL (auto_item).
 */
#[AsFrontendModule(type: 'tourtimes_reader', category: 'tourtimes', template: 'frontend_module/tourtimes_reader')]
class OfferReaderController extends AbstractTourTimesController
{
    private const RENDERED = '_tourtimes_reader_rendered';

    public function __construct(
        OfferRepository $repository,
        OfferViewBuilder $viewBuilder,
        ContentUrlGenerator $urlGenerator,
        Studio $studio,
        private readonly ResponseContextAccessor $responseContextAccessor,
        private readonly HtmlDecoder $htmlDecoder,
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($repository, $viewBuilder, $urlGenerator, $studio);
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        // ohne "keepUnused": damit gilt der Alias als verwendet, sonst antwortet Contao mit 404
        $alias = (string) Input::get('auto_item');

        // ohne Alias (z. B. Seite direkt aufgerufen) bleibt das Modul leer
        if ('' === $alias) {
            return new Response();
        }

        // ein Angebot nur einmal je Seitenaufruf ausgeben, auch wenn das Modul mehrfach eingebunden ist
        $main = $this->requestStack->getMainRequest();

        if (null !== $main) {
            if ($main->attributes->get(self::RENDERED) === $alias) {
                return new Response();
            }

            $main->attributes->set(self::RENDERED, $alias);
        }

        $offer = $this->repository->findPublishedByAlias($alias);

        if (null === $offer) {
            throw new PageNotFoundException('Angebot nicht gefunden: '.$alias);
        }

        $now = new DateTimeImmutable('now', OfferViewBuilder::timezone());
        $labels = $this->labels();
        $upcoming = (int) $model->row()['tourtimes_upcoming'];
        $views = $this->viewBuilder->buildOffers(
            [$offer],
            $now,
            $upcoming,
            '' !== (string) $model->row()['tourtimes_show_cancelled'],
            $labels['day_short'] ?? [],
            false,
        );
        $view = $this->decorate($views, $model, true)[0];

        $head = $this->responseContextAccessor->getResponseContext()?->get(HtmlHeadBag::class);

        if ($head instanceof HtmlHeadBag) {
            $head->setTitle($offer['title']);

            if ('' !== $offer['teaser']) {
                $head->setMetaDescription($this->htmlDecoder->inputEncodedToPlainText($offer['teaser']));
            }
        }

        $template->set('labels', $labels);
        $template->set('offer', $view);
        $template->set('show_upcoming', $upcoming > 0);
        $template->set('schema', $this->schema([$view], $model));

        $this->tagResponse([...self::TAGS, 'contao.db.tl_tt_offer.'.$offer['id']]);

        $response = $template->getResponse();
        $response->setSharedMaxAge(OfferViewBuilder::maxAge($now, OfferViewBuilder::nextUpcomingChange($views, $now)));

        return $response;
    }
}
