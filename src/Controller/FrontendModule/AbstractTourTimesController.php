<?php

declare(strict_types=1);

namespace Derkosmonaut\TourTimesBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Derkosmonaut\TourTimesBundle\Output\OfferViewBuilder;
use Derkosmonaut\TourTimesBundle\Repository\OfferRepository;

/**
 * Gemeinsame Helfer der Frontend-Module: Texte, Links zur Detailseite und Bilder.
 */
abstract class AbstractTourTimesController extends AbstractFrontendModuleController
{
    /** Cache-Tags: jede Änderung im Backend leert die Ausgabe. */
    protected const TAGS = ['contao.db.tl_tt_offer', 'contao.db.tl_tt_schedule', 'contao.db.tl_tt_slot', 'contao.db.tl_tt_exception', 'contao.db.tl_tt_cancellation'];

    public function __construct(
        protected readonly OfferRepository $repository,
        protected readonly OfferViewBuilder $viewBuilder,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly Studio $studio,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    protected function labels(): array
    {
        System::loadLanguageFile('tourtimes');

        return $GLOBALS['TL_LANG']['tourtimes'] ?? [];
    }

    /**
     * "In der Detailansicht ausblenden": ist ein Angebot aufgerufen, bleibt das Modul leer.
     * Der Alias wird dabei nicht als verwendet markiert, das übernimmt die Detailansicht.
     */
    protected function hiddenInReader(ModuleModel $model): bool
    {
        return '' !== (string) ($model->row()['tourtimes_hide_in_reader'] ?? '')
            && '' !== (string) Input::get('auto_item', false, true);
    }

    /**
     * @return list<int>
     */
    protected function offerIds(ModuleModel $model): array
    {
        return array_values(array_filter(array_map('intval', StringUtil::deserialize($model->row()['tourtimes_offers'] ?? null, true))));
    }

    /**
     * Ergänzt Detail-Link und Bild der Angebote.
     *
     * @param list<array<string, mixed>> $offers
     *
     * @return list<array<string, mixed>>
     */
    protected function decorate(array $offers, ModuleModel $model, bool $withImage): array
    {
        $page = (int) $model->jumpTo > 0 ? PageModel::findById((int) $model->jumpTo) : null;
        $size = StringUtil::deserialize($model->imgSize ?? null);

        foreach ($offers as $i => $offer) {
            $offers[$i]['url'] = null !== $page ? $this->detailUrl($page, $offer) : null;
            $offers[$i]['figure'] = $withImage ? $this->figure($offer['image'] ?? null, $size) : null;
        }

        return $offers;
    }

    /**
     * schema.org-Events der angezeigten Termine, sofern im Modul aktiviert.
     *
     * @param list<array<string, mixed>> $offers
     *
     * @return list<array<string, mixed>>
     */
    protected function schema(array $offers, ModuleModel $model): array
    {
        if ('' === (string) ($model->row()['tourtimes_jsonld'] ?? '')) {
            return [];
        }

        return array_merge(...array_map(OfferViewBuilder::schemaEvents(...), $offers));
    }

    /**
     * @param array<string, mixed> $offer
     */
    private function detailUrl(PageModel $page, array $offer): ?string
    {
        $item = '' !== (string) ($offer['alias'] ?? '') ? (string) $offer['alias'] : (string) $offer['id'];

        try {
            return $this->urlGenerator->generate($page, ['parameters' => '/'.$item]);
        } catch (\Throwable) {
            return null;
        }
    }

    private function figure(?string $uuid, mixed $size): ?Figure
    {
        if (null === $uuid) {
            return null;
        }

        return $this->studio->createFigureBuilder()
            ->fromUuid($uuid)
            ->setSize(\is_array($size) || \is_string($size) || \is_int($size) ? $size : null)
            ->buildIfResourceExists()
        ;
    }
}
